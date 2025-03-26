import { ref } from 'vue';
import axios from 'axios';

export function useAI() {
    const response = ref('');
    const isLoading = ref(false);
    const error = ref(null);
    const conversationHistory = ref([]);
    const chatHistory = ref([]);
    const loadingHistory = ref(false);
    const currentChatId = ref(null);

    const sendPrompt = async (prompt, chatId) => {
        isLoading.value = true;
        error.value = null;

        try {
            // Add the new message to conversation history
            conversationHistory.value.push({
                role: 'user',
                content: prompt
            });

            // Check if the prompt is asking to add to cart
            const addToCartMatch = prompt.match(/add\s+(.+?)\s+to\s+(cart|basket)/i);
            if (addToCartMatch) {
                const productName = addToCartMatch[1];
                // First, check if we can find product info
                const { data } = await axios.post('/ai/process', {
                    prompt: `Find product information for: ${productName}`
                });

                const aiResponse = data.candidates?.[0]?.content?.parts?.[0]?.text;

                // Look for product ID in the response
                const productIdMatch = aiResponse.match(/Product ID: (\d+)/);
                if (productIdMatch) {
                    const productId = productIdMatch[1];

                    // Add to cart
                    const cartResponse = await axios.post('/ai/add-to-cart', {
                        product_id: productId
                    });

                    if (cartResponse.data.success) {
                        // Add AI response to conversation history
                        const successMessage = `${cartResponse.data.message} <a href="${cartResponse.data.cartUrl}" class="text-navy-600 underline">View Cart</a>`;
                        conversationHistory.value.push({
                            role: 'assistant',
                            content: successMessage
                        });
                        response.value = successMessage;
                        return successMessage;
                    } else {
                        // If not logged in, prompt to login
                        let errorMessage = cartResponse.data.message;
                        if (cartResponse.data.loginUrl) {
                            errorMessage += ` <a href="${cartResponse.data.loginUrl}" class="text-navy-600 underline">Login here</a>`;
                        }
                        conversationHistory.value.push({
                            role: 'assistant',
                            content: errorMessage
                        });
                        response.value = errorMessage;
                        return errorMessage;
                    }
                }
            }

            // Create the base context
            let enrichedPrompt = `You are an AI chatbot for a E-Commerce website named DRM Roofing, Glass, and Iron Works and
                your name is Chatter.
                You are a helpful assistant that can help the customers with their needs like navigation, products, categories, and suggestion
                for what they want to do to the products they want to buy.
                You are also able to help the user with their questions and concerns about the products they want to buy.
                You are unable to help about outside topics of the website or anything that is not related to the products they want to buy.

                WEBSITE CONTEXT:
                Navigation:
                - Home
                - Products
                - About Us
                - Contact Us
                There is a search bar in the header navigation bar.
                Login Button in the right side of the header navigation bar if the user is not logged in.
                If the user is logged in, the login button will be replaced with the user's name with a dropdown menu for logout button.
                There is a cart icon in the right side of the header navigation bar.

                Home Page Sections:
                - Hero Section
                - Features Section
                - Latest Products Section
                - Categories Section
                - Explore Our Products Section
                - Testimonials Section
                - Footer

                COMPREHENSIVE PRODUCT SEARCH CAPABILITIES:
                - When a user asks about products, you can search the catalog for specific products
                - You can search by product name, category, brand, or general description
                - If the user asks for products in a specific category, provide category links and relevant product examples
                - If the user asks for products by a specific brand, provide brand-filtered products
                - Always provide direct product links for users to view the products

                PRODUCT INFORMATION DETAILS:
                - Provide detailed product information including name, price, specifications, and stock availability
                - Format prices clearly with the ₱ currency symbol
                - Include clickable product links to the product page (format: http://127.0.0.1:8000/product-list/[slug])
                - When specifications are available, list them in an organized manner
                - Provide stock information to let users know if products are available

                IMPORTANT GUIDELINES ON PRODUCT INFORMATION:
                - ONLY discuss products that actually exist in our database
                - NEVER make up or fabricate product information
                - If you don't have information about a specific product, direct users to browse our categories instead
                - Always use the product data provided to you through the backend search
                - If a user asks about a product you don't have information on, say "I don't have information on that specific product"
                - Recommend browsing category pages when specific product searches don't yield results

                NAVIGATION AND CART ASSISTANCE:
                - If the customer wants to go to the cart page provide this link: http://127.0.0.1:8000/cart
                - Format all links in an href of an anchor tag with blue color
                - If the user wants to add a product to cart, provide the product page link with format http://127.0.0.1:8000/product-list/[slug] in an <a> tag with blue color
                - If the user wants to browse categories, provide category links with format http://127.0.0.1:8000/category-products/[slug] in an <a> tag with blue color

                Products Page Features:
                - Price range filter
                - Brand filter
                - Category filter
                - Availability filter

                CONVERSATION HISTORY:
                ${conversationHistory.value
                    .map(msg => `${msg.role === 'user' ? 'Customer' : 'Chatter'}: ${msg.content}`)
                    .join('\n')}

                Remember to:
                1. Maintain context from the conversation history
                2. Be friendly and helpful
                3. Stay focused on the website and products
                4. Ask follow-up questions when needed for clarity
                5. Provide specific product or navigation suggestions based on the customer's needs
                6. Always format product links as clickable HTML links that open in a new tab
                7. Present product information in a well-organized, easy-to-read format
                8. If a user asks about adding products to cart, provide the direct product link
                9. Use HTML formatting for better presentation (bold, lists, etc.)
                10. If no products match a specific query, suggest alternatives or recommend browsing categories

                For product searches, you can interpret requests like:
                - "Show me roofing products" - search for products in roofing category
                - "Find door hinges" - search for door hinge products
                - "What glass products do you have?" - search for glass category products
                - "Show me iron works" - search for ironwork products
                - "What categories do you have?" - list all product categories
                - "I need a new door" - search for door products
                - "Looking for cheap roofing materials" - search for affordable roofing products
                - "Do you have any premium glass options?" - search for high-end glass products
                - "What's in stock?" - search for products that are currently available

                Now please respond to the customer's latest message.`;

            // Send the enriched prompt to the AI
            const { data } = await axios.post('/ai/process', {
                newprompt: enrichedPrompt,
                prompt: prompt,
                chatId: currentChatId.value
            });

            if (data.error) {
                error.value = data.error;
                return null;
            }

            // Update the current chat ID from the response
            if (data.chatId) {
                currentChatId.value = data.chatId;
            }

            const aiResponse = data.candidates?.[0]?.content?.parts?.[0]?.text;
            if (aiResponse) {
                // Add AI response to conversation history
                conversationHistory.value.push({
                    role: 'assistant',
                    content: aiResponse
                });
                response.value = aiResponse;
                return aiResponse;
            } else {
                error.value = 'No response from AI';
                return null;
            }
        } catch (err) {
            console.error('AI Error:', err);
            error.value = err.response?.data?.error || 'Error processing AI request';
            return null;
        } finally {
            isLoading.value = false;
        }
    };

    // Add method to clear conversation history
    const clearConversation = () => {
        conversationHistory.value = [];
        currentChatId.value = null;
    };

    // Get chat history
    const fetchChatHistory = async () => {
        try {
            loadingHistory.value = true;
            const { data } = await axios.get('/ai/chat-history');
            chatHistory.value = data.history;
            return data.history;
        } catch (err) {
            console.error('Error fetching chat history:', err);
            return null;
        } finally {
            loadingHistory.value = false;
        }
    };

    // Load a specific conversation
    const loadConversation = async (promptId) => {
        try {
            loadingHistory.value = true;
            const { data } = await axios.get(`/ai/conversation/${promptId}`);

            if (data.error) {
                console.error('Error loading conversation:', data.error);
                return false;
            }

            // Update current chat ID
            currentChatId.value = promptId;

            // Clear current conversation and add all prompts from this history
            conversationHistory.value = [];

            if (data.prompts && data.prompts.length > 0) {
                // Convert prompts to conversation format
                data.prompts.forEach(prompt => {
                    conversationHistory.value.push(
                        { role: 'user', content: prompt.prompt },
                        { role: 'assistant', content: prompt.response }
                    );
                });
            }

            return true;
        } catch (err) {
            console.error('Failed to load conversation:', err);
            return false;
        } finally {
            loadingHistory.value = false;
        }
    };

    // Delete a specific conversation
    const deleteConversation = async (promptId) => {
        try {
            const response = await axios.delete(`/ai/chat-history/${promptId}`);
            if (response.data.success) {
                // If we're deleting the current conversation, clear it
                if (currentChatId.value === promptId) {
                    clearConversation();
                }

                // Remove from local chat history
                for (const date in chatHistory.value) {
                    const index = chatHistory.value[date].findIndex(item => item.id === promptId);
                    if (index !== -1) {
                        chatHistory.value[date].splice(index, 1);
                        // If this date has no more conversations, remove the date key
                        if (chatHistory.value[date].length === 0) {
                            delete chatHistory.value[date];
                        }
                        return true;
                    }
                }
            }
            return false;
        } catch (err) {
            console.error('Failed to delete conversation:', err);
            return false;
        }
    };

    return {
        response,
        isLoading,
        error,
        sendPrompt,
        clearConversation,
        fetchChatHistory,
        loadConversation,
        deleteConversation,
        chatHistory,
        loadingHistory,
        conversationHistory,
        currentChatId
    };
}
