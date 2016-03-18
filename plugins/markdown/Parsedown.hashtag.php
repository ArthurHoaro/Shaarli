<?php

class ParsedownHashtag extends Parsedown
{
    protected $separatedInlineMarkerList = array();

    protected $avoidNext = false;

    /**
     * Constructor used to add new parsable elements.
     */
    function __construct()
    {
        $marker = ' #';
        $this->InlineTypes[$marker][] = 'Hashtag';
        $this->specialInlineMarkerList[] = $marker;

        $this->InlineTypes['#'] []= 'Hashtag';
        $this->inlineMarkerList .= '#';
    }

    /**
     * Override block header (#) to handle hashtags on new line.
     *
     * Warning: this changes the default behaviour of Parsedown implementation.
     * #hashtag won't generate a header while it does without this extension.
     *
     * Note that this previous behaviour was only an implementation choice,
     * and is invalid regarding CommonMark specifications:
     * http://spec.commonmark.org/0.24/#atx-headings
     *
     * @param array $line block content.
     *
     * @return array Parsedown data, either a header or a hashtag.
     */
    protected function blockHeader($line) {
        if (! preg_match('/^#([^\s]+)(.*)/', trim($line['text']), $matches)) {
            return parent::blockHeader($line);
        }
    }

    protected function inlineHashtag(&$excerpt)
    {
        if ($this->avoidNext) {
            $this->avoidNext = false;
            return;
        }

        if (preg_match('/^#([a-z0-9]+)([^a-z0-9]?)/i', $excerpt['text'], $matches)) {
            $length = strlen($matches[0]);
            // Workaround to handle #tag1#tag2 cases (tag2 should be ignored).
            if (!empty($matches[2])) {
                $length--;
                if ($matches[2] == '#') {
                    $this->avoidNext = true;
                }
            }

            // Get the character before the supposed hashtag
            // and make sure it's a new line or a space.
            $pos = strpos($excerpt['context'], $excerpt['text']);
            if ($pos !== false && ($pos == 0 || preg_match('/\s/', $excerpt['context'][$pos - 1]))) {
                return array(
                    'extent' => $length,
                    'element' => $this->buildHashtagElement($matches[1]),
                );
            }
        }
    }

    private function buildHashtagElement($hashtag, $handler = false)
    {
         $element = array(
            'name' => 'a',
            'text' => '#'. $hashtag,
            'attributes' => array(
                'href' => '?hashtag='. $hashtag,
                'title' => 'Hashtag '. $hashtag,
                'class' => 'hashtag',
            ),
        );

        if ($handler) {
            $element['handler'] = $handler;
        }

        return $element;
    }
}