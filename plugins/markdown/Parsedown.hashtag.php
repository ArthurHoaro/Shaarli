<?php

class ParsedownHashtag extends Parsedown
{
    /**
     * Constructor used to add new parsable elements.
     */
    function __construct()
    {
        $this->InlineTypes['#'][] = 'Hashtag';
        $this->inlineMarkerList .= '#';
    }

    /**
     * Override block header (#) to handle hashtag on new lines.
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
        if (! preg_match('/^#([^\s]+)/', trim($line['text']), $matches)) {
            return parent::blockHeader($line);
        }

        $link = array(
            'name' => 'a',
            'text' =>  '#'. $matches[1],
            'handler' => 'line',
            'attributes' => array(
                'href' => '#',
                'title' => 'Hashtag',
                'class' => 'hashtag'
            ),
        );

        $element = array(
            'element' => array(
                'name' => 'p',
                'handler' =>  'elements',
                'text' => array($link),
            ),
        );

        return $element;
    }
}