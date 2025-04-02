<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Prompt;
use App\Models\PromptHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AiController extends Controller
{
    public function processPrompt(Request $request)
    {
        try {
            $apiKey = env('GEMINI_API_KEY');

            if (!$apiKey) {
                Log::error('Gemini API key is not set');
                return response()->json([
                    'error' => 'API key is not configured'
                ], 500);
            }

            // Gather product information to provide to the AI
            $productInfo = $this->getProductInfo($request->input('prompt'));

            // Enrich prompt with product information if relevant
            $enrichedPrompt = $request->input('newprompt');

            // If product information was found, add it to the prompt
            if ($productInfo) {
                $enrichedPrompt .= "\n\nRELEVANT PRODUCT INFO FROM DATABASE:\n" . $productInfo;
                $enrichedPrompt .= "\n\nIMPORTANT: Only discuss products shown above or products you know exist in our database. DO NOT make up or fabricate product information that isn't provided here. If asked about products not in our database, recommend browsing our categories instead.";
            } else {
                // If no specific product info was found, add a note to avoid fabricating products
                $enrichedPrompt .= "\n\nIMPORTANT: You don't have specific product information for this query. DO NOT make up or fabricate product details. Instead, guide the user to browse our product categories or suggest they try a different search term.";
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $enrichedPrompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 2048,
                ]
            ]);

            if (!$response->successful()) {
                Log::error('Gemini API error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return response()->json([
                    'error' => 'Failed to get AI response'
                ], $response->status());
            }

            $data = $response->json();

            // Extract the response text from the correct path in the response
            $responseText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$responseText) {
                Log::error('Invalid response format from Gemini API', ['response' => $data]);
                return response()->json([
                    'error' => 'Invalid response format from AI'
                ], 500);
            }

            // Process the response text to preserve formatting
            $formattedText = $this->formatResponse($responseText);

            // Handle chat history management
            $chatId = $request->input('chatId');

            // If we don't have a chat ID in the session or the provided one is invalid,
            // we need to create a new conversation
            if (!session()->has('chat_id') || !$chatId) {
                // Create a new conversation
                $newChatId = $this->savePromptHistory($request->input('prompt'), $formattedText);
                $chatId = $newChatId;
            } else {
                // Add to existing conversation
                Prompt::create([
                    'prompt' => $request->input('prompt'),
                    'response' => $formattedText,
                    'history_id' => $chatId,
                ]);
            }

            return response()->json([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => $formattedText]
                            ]
                        ]
                    ]
                ],
                'chatId' => $chatId
            ]);

        } catch (\Exception $e) {
            Log::error('AI processing error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error processing request'
            ], 500);
        }
    }

    /**
     * Check if prompt is requesting product information and retrieve it
     */
    private function getProductInfo($prompt)
    {
        // Extract product-related keywords from the prompt
        $prompt = strtolower($prompt);

        // Normalize the prompt for better matching
        $prompt = preg_replace('/[^\w\s]/', ' ', $prompt);
        $prompt = preg_replace('/\s+/', ' ', $prompt);

        // Check if user is asking for general product categories
        if (str_contains($prompt, 'categories') ||
            str_contains($prompt, 'what products') ||
            str_contains($prompt, 'what types') ||
            str_contains($prompt, 'what kind') ||
            str_contains($prompt, 'show all')) {

            // Return category information
            $categories = DB::table('categories')
                ->select('name', 'id', 'slug')
                ->get();

            if ($categories->count() > 0) {
                $info = "We offer products in these categories:\n\n";
                foreach ($categories as $category) {
                    // Only create links for categories that actually have a slug
                    if (!empty($category->slug)) {
                        $categoryLink = "https://drm-hardware.com/category-products/{$category->slug}";
                        $info .= "- **{$category->name}**: [Browse all {$category->name}]({$categoryLink})\n";
                    } else {
                        $info .= "- **{$category->name}**\n";
                    }
                }

                $info .= "\nYou can click on any category link to browse products. Is there a specific category you're interested in?";
                return $info;
            }
        }

        // Check if the prompt is asking about products and prices
        $productKeywords = [
            'product', 'price', 'how much', 'cost', 'buy', 'purchase', 'available',
            'show me', 'find', 'search', 'look for', 'looking for', 'want to buy',
            'interested in', 'do you have', 'do you sell', 'tell me about'
        ];

        $isProductQuery = false;
        foreach ($productKeywords as $keyword) {
            if (str_contains($prompt, $keyword)) {
                $isProductQuery = true;
                break;
            }
        }

        if ($isProductQuery) {
            // Search for product categories mentioned
            $categoryMatches = [];
            $categories = DB::table('categories')->pluck('name')->toArray();
            foreach ($categories as $category) {
                if (str_contains(strtolower($prompt), strtolower($category))) {
                    $categoryMatches[] = $category;
                }
            }

            // Search for brand names mentioned
            $brandMatches = [];
            $brands = DB::table('brands')->pluck('name')->toArray();
            foreach ($brands as $brand) {
                if (str_contains(strtolower($prompt), strtolower($brand))) {
                    $brandMatches[] = $brand;
                }
            }

            // Start building product query
            $query = Product::query();

            // If specific categories were mentioned, filter by those
            if (!empty($categoryMatches)) {
                $query->whereHas('category', function($q) use ($categoryMatches) {
                    $q->whereIn('name', $categoryMatches);
                });
            }

            // If specific brands were mentioned, filter by those
            if (!empty($brandMatches)) {
                $query->whereHas('brand', function($q) use ($brandMatches) {
                    $q->whereIn('name', $brandMatches);
                });
            }

            // Extract potential product name from the prompt
            $productNameMatches = [];
            // Expanded regex pattern to catch more search patterns
            preg_match('/(?:find|show me|about|get|search for|looking for|need|want|interested in|tell me about|do you have)\s+(?:a|an|the|some|any)?\s*([a-zA-Z0-9\s\-\+]+?)(?:\s+(?:from|in|by|that|which|with|for|under|below|above|over|around|about)|\?|\s*$)/i', $prompt, $productNameMatches);

            // If direct pattern match failed, try a more general approach
            if (empty($productNameMatches[1])) {
                // Remove common filler words to extract potential product names
                $fillerWords = ['is', 'are', 'a', 'an', 'the', 'this', 'that', 'these', 'those', 'of', 'for', 'with', 'about', 'like', 'can', 'you', 'i', 'me', 'my', 'show', 'find', 'get', 'want', 'need', 'search', 'looking', 'from', 'have', 'has', 'do', 'does'];
                $words = explode(' ', $prompt);
                $filteredWords = array_filter($words, function($word) use ($fillerWords) {
                    return !in_array(strtolower($word), $fillerWords) && strlen($word) > 2;
                });

                if (!empty($filteredWords)) {
                    $searchTerm = implode(' ', $filteredWords);
                    $query->where(function($q) use ($searchTerm, $filteredWords) {
                        // Search in name field with the full filtered string
                        $q->where('name', 'like', '%' . $searchTerm . '%');

                        // Also search for individual keywords in the description
                        foreach ($filteredWords as $word) {
                            if (strlen($word) > 3) { // Skip very short words
                                $q->orWhere('description', 'like', '%' . $word . '%');
                            }
                        }
                    });
                }
            } else {
                $searchTerm = trim($productNameMatches[1]);
                if (strlen($searchTerm) > 2) { // Avoid searching for very short terms
                    // Search in both name and description
                    $query->where(function($q) use ($searchTerm) {
                        $q->where('name', 'like', '%' . $searchTerm . '%')
                          ->orWhere('description', 'like', '%' . $searchTerm . '%');
                    });
                }
            }

            // Add price filter if mentioned
            if (str_contains($prompt, 'cheap') || str_contains($prompt, 'affordable') || str_contains($prompt, 'budget') || str_contains($prompt, 'low price') || str_contains($prompt, 'inexpensive')) {
                $query->orderBy('price', 'asc');
            } else if (str_contains($prompt, 'expensive') || str_contains($prompt, 'premium') || str_contains($prompt, 'high quality') || str_contains($prompt, 'luxury')) {
                $query->orderBy('price', 'desc');
            }

            // Add stock filter if mentioned
            if (str_contains($prompt, 'in stock') || str_contains($prompt, 'available now') || str_contains($prompt, 'ready to ship')) {
                $query->where('stock', '>', 0);
            }

            // Get the products with their relationships
            $products = $query->with(['category', 'brand', 'specifications'])->take(5)->get();

            // Build product information response
            if ($products->count() > 0) {
                $info = "Here are some products that match your query (found in our database):\n\n";
                foreach ($products as $product) {
                    // Only create links for products that actually have a slug
                    if (!empty($product->slug)) {
                        $productLink = "https://drm-hardware.com/product-list/{$product->slug}";
                    } else {
                        // Log this issue for admin awareness
                        Log::warning("Product without slug found: ID {$product->id}, Name: {$product->name}");
                        continue; // Skip products without slugs
                    }

                    $info .= "**{$product->name}** - ₱{$product->price}\n";

                    // Only include category and brand if they exist
                    if ($product->category) {
                        $info .= "Category: {$product->category->name}";
                    }

                    if ($product->brand) {
                        $info .= $product->category ? " | " : "";
                        $info .= "Brand: {$product->brand->name}";
                    }

                    $info .= "\n";

                    // Only include description if it exists
                    if (!empty($product->description)) {
                        $info .= "Description: " . substr($product->description, 0, 100) . "...\n";
                    }

                    $info .= "Stock: " . ($product->stock > 0 ? "{$product->stock} available" : "Out of stock") . "\n";

                    // Add specifications if available
                    if ($product->specifications && $product->specifications->count() > 0) {
                        $info .= "Specifications:\n";
                        foreach ($product->specifications as $spec) {
                            $value = '';
                            if (isset($spec->pivot) && !empty($spec->pivot->value)) {
                                $value = $spec->pivot->value;
                            } elseif (method_exists($spec, 'value') && !empty($spec->value())) {
                                $value = $spec->value();
                            }

                            if (!empty($value) && !empty($spec->name)) {
                                $info .= "• {$spec->name}: {$value}\n";
                            }
                        }
                    }

                    $info .= "View Product: [Click here to view or buy]({$productLink})\n\n";
                }

                $info .= "These are actual products from our database. You can click on the links above to view product details or add items to your cart.";
                return $info;
            } else {
                // If no specific products were found based on the search terms,
                // get some general product recommendations but limit the response
                $products = Product::with(['category', 'brand'])
                    ->whereNotNull('slug') // Ensure products have slugs
                    ->where('slug', '!=', '')
                    ->whereNotNull('product_images') // Ensure products have images
                    ->take(5) // Limit to 5 products to keep the response focused
                    ->orderBy('stock', 'desc')
                    ->get();

                if ($products->count() > 0) {
                    $info = "I couldn't find specific products matching your query, but here are some popular items from our inventory:\n\n";

                    foreach ($products as $product) {
                        if (empty($product->slug)) {
                            continue; // Skip products without slugs
                        }

                        $productLink = "https://drm-hardware.com/product-list/{$product->slug}";

                        $info .= "**{$product->name}** - ₱{$product->price}\n";

                        // Only include category and brand if they exist
                        if ($product->category) {
                            $info .= "Category: {$product->category->name}";
                        }

                        if ($product->brand) {
                            $info .= $product->category ? " | " : "";
                            $info .= "Brand: {$product->brand->name}";
                        }

                        $info .= "\n";

                        // Include stock information
                        $info .= "Stock: " . ($product->stock > 0 ? "{$product->stock} available" : "Out of stock") . "\n";
                        $info .= "View Product: [Click here to view or buy]({$productLink})\n\n";
                    }

                    // Suggest browsing categories as an alternative
                    $info .= "You may also want to browse our product categories:\n";
                    $categories = DB::table('categories')
                        ->select('name', 'slug')
                        ->whereNotNull('slug')
                        ->where('slug', '!=', '')
                        ->take(5)
                        ->get();

                    foreach ($categories as $category) {
                        $categoryLink = "https://drm-hardware.com/category-products/{$category->slug}";
                        $info .= "- [Browse all {$category->name}]({$categoryLink})\n";
                    }

                    return $info;
                } else {
                    // If we don't have any products at all, provide a generic message
                    return "I couldn't find any products in our database matching your query. Please try browsing our product categories on the homepage.";
                }
            }
        }

        return null;
    }

    /**
     * Save the prompt and response to history
     */
    private function savePromptHistory($prompt, $response)
    {
        try {
            // Generate a title from the first prompt, limited to 255 characters
            $title = substr($prompt, 0, 255);

            // Create a new prompt history entry
            $history = PromptHistory::create([
                'user_id' => Auth::guard('customer')->id(),
                'title' => $title
            ]);

            // Create the first prompt in this history
            Prompt::create([
                'prompt' => $prompt,
                'response' => $response,
                'history_id' => $history->id,
            ]);

            // Update session with new chat ID
            session()->put('chat_id', $history->id);

            return $history->id;
        } catch (\Exception $e) {
            Log::error('Failed to save prompt history: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get chat history for the authenticated user
     */
    public function getChatHistory()
    {
        if (!Auth::guard('customer')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $history = PromptHistory::where('user_id', Auth::guard('customer')->id())
            ->select('id', 'title', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function($date) {
                return $date->created_at->format('Y-m-d');
            });

        return response()->json(['history' => $history]);
    }

    /**
     * Get prompts for a specific conversation
     */
    public function getConversation($id)
    {
        if (!Auth::guard('customer')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $history = PromptHistory::where('id', $id)
                ->where('user_id', Auth::guard('customer')->id())
                ->firstOrFail();

            $prompts = Prompt::where('history_id', $id)
                ->orderBy('created_at')
                ->get();

            return response()->json([
                'history' => $history,
                'prompts' => $prompts
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get conversation: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to retrieve conversation'
            ], 500);
        }
    }

    private function formatResponse($text)
    {
        // Convert headings to proper HTML but with more natural styling
        $text = preg_replace('/^### (.*?)$/m', '<div class="font-bold mt-2 mb-1">$1</div>', $text);
        $text = preg_replace('/^## (.*?)$/m', '<div class="font-bold mt-3 mb-1">$1</div>', $text);
        $text = preg_replace('/^# (.*?)$/m', '<div class="font-bold text-lg mt-3 mb-2">$1</div>', $text);

        // Preserve paragraph breaks
        $text = preg_replace('/\n\s*\n/', "\n\n", $text);

        // Convert strong/bold formatting
        $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);

        // Convert italic formatting
        $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);

        // Convert markdown links to HTML with styled classes and ensure URLs are properly encoded
        $text = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/', function($matches) {
            $linkText = htmlspecialchars($matches[1]);
            $url = htmlspecialchars($matches[2]);

            // Ensure URL is properly formatted and has a protocol
            if (!preg_match('/^https?:\/\//', $url)) {
                $url = 'https://' . $url;
            }

            // Verify it's a valid URL
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                return '<a href="' . $url . '" class="text-navy-600 underline hover:text-navy-800" target="_blank">' . $linkText . '</a>';
            } else {
                // If the URL is invalid, just return the text without making it a link
                return $linkText;
            }
        }, $text);

        // Convert bullet points to simple formatted text rather than HTML lists
        $text = preg_replace('/^  • (.*?)$/m', '• $1', $text);
        $text = preg_replace('/^• (.*?)$/m', '• $1', $text);
        $text = preg_replace('/^  - (.*?)$/m', '• $1', $text);
        $text = preg_replace('/^- (.*?)$/m', '• $1', $text);

        // Preserve code blocks and inline code
        $text = preg_replace('/```(.*?)```/s', '<code style="display: inline-block; background-color: #f5f5f5; padding: 2px 4px; border-radius: 3px;">$1</code>', $text);
        $text = preg_replace('/`(.*?)`/', '<code style="background-color: #f5f5f5; padding: 1px 3px; border-radius: 3px;">$1</code>', $text);

        // Handle line breaks within paragraphs, preserving intentional breaks
        $text = preg_replace('/([^>\n])\n(?!<)/', '$1<br>', $text);

        return trim($text);
    }

    /**
     * Add a product to the cart
     */
    public function addToCart(Request $request)
    {
        try {
            $productId = $request->input('product_id');
            $product = Product::findOrFail($productId);

            // Add to cart logic using existing CartController logic
            if (Auth::guard('customer')->check()) {
                // Use CartItem model directly
                $cartItem = \App\Models\CartItem::where('customer_id', Auth::guard('customer')->id())
                    ->where('product_id', $productId)
                    ->first();

                if ($cartItem) {
                    $cartItem->update([
                        'quantity' => $cartItem->quantity + 1
                    ]);
                } else {
                    \App\Models\CartItem::create([
                        'customer_id' => Auth::guard('customer')->id(),
                        'product_id' => $productId,
                        'quantity' => 1,
                        'price' => $product->price,
                        'selected' => true
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => "{$product->name} added to your cart!",
                    'cartUrl' => route('cart.index')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login to add items to your cart.',
                    'loginUrl' => route('customer.login')
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Add to cart error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add product to cart. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete a specific prompt history
     */
    public function deletePromptHistory($id)
    {
        if (!Auth::guard('customer')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $promptHistory = PromptHistory::where('id', $id)
                ->where('user_id', Auth::guard('customer')->id())
                ->firstOrFail();

            // If we're deleting the current active chat, clear the session
            if (session('chat_id') == $id) {
                session()->forget('chat_id');
            }

            $promptHistory->delete();

            return response()->json([
                'success' => true,
                'message' => 'Conversation deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete prompt history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete conversation'
            ], 500);
        }
    }
}

