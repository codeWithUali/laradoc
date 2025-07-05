<?php

if (!function_exists('marked')) {
    /**
     * Convert markdown to HTML using marked.js
     *
     * @param string $markdown
     * @return string
     */
    function marked($markdown)
    {
        // For now, return the markdown as-is since we're using marked.js in the frontend
        // In a production environment, you might want to use a PHP markdown parser
        return $markdown;
    }
} 