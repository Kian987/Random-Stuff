<?php

// It doesn't have to be a function. Just run it

function Yeah()
{
    $backlist = array('http://www.mediafire.com', 'http://google.com'); // Enter URLs (scheme eg. http + host eg. google.com) you want to remove from posts
    $replaceWith = '**CENSORED**'; // Every time the script finds a match (eg. http://google.com/whatever, http://google.com/hello-world) it replaces the entire URL block with the given string. If you don't want to replace anything just set this variable to false

    $dom = new \DomDocument();
    $dom->encoding = 'utf-8';

    // I'm selecting from database all posts (id and post_content columns) so that I can scan everything to "blacklist" the given URLs. Here I'm using PDO and an object where I have already established a connection with database. Change it accordingly
    $query = $this->mysql->prepare('SELECT id, post_content FROM wp_post');
    $query->execute();
    while($row = $query->fetch())
    {
        // I parse post_content with DOM
        $dom->loadHTML(utf8_decode($row->post_content), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // Finding all <a href=""></a>
        $elements = $dom->getElementsByTagName('a');

        // Counting how many <a href=""></a> I found in this post_content...
        $i = $elements->length - 1;

        // ... so that I can analyze each of them
        while ($i > -1)
        {
            // Retreiving the actual value of href attribute
            $element = $elements->item($i);
            $url = $element->getAttribute('href');

            // If the given URL is part of your $blacklist I replace it with your $replaceWith (eg. **CENSORED**)
            if (in_array(str_replace('%5C%22', '', $url), $backlist))
            {
                $newelement = $dom->createTextNode($replaceWith);
                $element->parentNode->replaceChild($newelement, $element);
            }

            // Repeat for all other URLs in post_content
            $i--;
        }

        // At this point I analyzed all URLs removing blacklisted domains. It's now time to save the output in wp_post with this query
        $update = $this->mysql->prepare('UPDATE wp_post SET post_content = :post_content WHERE id = :id LIMIT 1');
        $update->execute(array('post_content' => $dom->saveHTML(), 'id' => $row->id));

        // Repeat for all other records in wp_post
    }
}
