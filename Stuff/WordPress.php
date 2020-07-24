function Yeah()
{
    $backlist = array('http://www.mediafire.com', 'http://google.com');
    $replaceWith = '**CENSORED**';

    $dom = new \DomDocument();
    $dom->encoding = 'utf-8';

    $query = $this->mysql->prepare('SELECT id, post_content FROM wp_post');
    $query->execute();
    while($row = $query->fetch())
    {
        $dom->loadHTML(utf8_decode($row->post_content), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $elements = $dom->getElementsByTagName('a');
        $i = $elements->length - 1;

        while ($i > -1)
        {
            $element = $elements->item($i);
            $url = $element->getAttribute('href');

            if (in_array(str_replace('%5C%22', '', $url), $backlist))
            {
                $newelement = $dom->createTextNode($replaceWith);
                $element->parentNode->replaceChild($newelement, $element);
            }

            $i--;
        }

        $update = $this->mysql->prepare('UPDATE wp_post SET post_content = :post_content WHERE id = :id LIMIT 1');
        $update->execute(array('post_content' => $dom->saveHTML(), 'id' => $row->id));
    }
}
