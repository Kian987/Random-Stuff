<?php

$content = <<<HTML
<ul>
    <li><a href="https://katamaze.com">https://katamaze.com</a></li>
    <li><a href="https://katamaze.it">https://katamaze.it</a></li>
    <li><a href="https://katamaze.com"><strong>https://katamaze.com</strong></a></li>
    <li><a href="http://katamaze.com"><strong>http://katamaze.com</strong></a></li>
    <li><a href="https://katamaze.com"><img src="path/to/image.png"></a></li>
</ul>
HTML;

echo 'Blacklisting <strong>katamaze.com</strong> from the list:' . PHP_EOL;
echo $content;
echo '<hr>';
echo 'Output:' . PHP_EOL;
echo ClearURL($content, array('katamaze.com'), '**CENSORED**');

function ClearURL($content, $blacklist = false, $replaceWith = false)
{
    if (!$blacklist): return $content; endif;

    $dom = new DomDocument();
    $dom->encoding = 'utf-8';
    $dom->loadHTML(utf8_decode($content), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    $elements = $dom->getElementsByTagName('a');
    $i = $elements->length - 1;

    while ($i > -1)
    {
        $element = $elements->item($i);
        $url = parse_url($element->getAttribute('href'))['host'];

        if (in_array($url, $blacklist))
        {
            $replace = $dom->createTextNode($replaceWith);
            $element->parentNode->replaceChild($replace, $element);
        }

        $i--;
    }

    return $dom->saveHTML();
}
