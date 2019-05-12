<?php

namespace AppBundle\Xml\Formatter;

use AppBundle\Document\Feed;
use AppBundle\Document\FeedItem;
use AppBundle\DomDocument\XDOMDocument;
use AppBundle\DomDocument\XDOMElement;

/**
 * Formatter.
 *
 * This class provides formatter methods
 */
abstract class Formatter
{
    /**
     * @var Feed A feed instance
     */
    protected $feed;

    /**
     * @var array A collection of item
     */
    protected $items;

    /**
     * @var string Generator name
     */
    protected $generator;

    /**
     * @var string Feed url
     */
    protected $url;

    /**
     * @var XDOMDocument XML (X)DOMDocument
     */
    protected $dom;

    /**
     * @var array Contain arrays for this formatter
     */
    protected $fields;

    /**
     * Construct a formatter with given feed.
     *
     * @param Feed        $feed      A feed instance
     * @param array       $items     An array of FeedItem object
     * @param string      $url
     * @param string|null $generator
     */
    public function __construct(Feed $feed, $items, $url, $generator = null)
    {
        $this->feed = $feed;
        $this->items = $items;
        $this->url = $url;
        $this->generator = $generator;

        $this->dom = new XDOMDocument('1.0', 'utf-8');

        $this->setItemFields();
        $this->initialize();
    }

    /**
     * Define fields that will inside an item
     *     - name: will be the node name
     *     - method: will be the method to retrieve content to put in this node.
     */
    abstract public function setItemFields();

    /**
     * Initialize XML DOMDocument nodes and call addItem on all items.
     */
    abstract public function initialize();

    /**
     * This method render the given feed transforming the DOMDocument to XML.
     *
     * @return string
     */
    public function render()
    {
        $this->dom->formatOutput = true;

        return $this->dom->saveXml();
    }

    /**
     * Add an entity item to the feed.
     *
     * @param XDOMElement $root The root (feed) DOM element
     * @param FeedItem    $item An entity object
     * @param string      $name Could be "entry", for atom or "item" for rss
     */
    public function addItem(XDOMElement $root, FeedItem $item, $name)
    {
        $node = $this->dom->createElement($name);
        $node = $root->appendChild($node);

        foreach ($this->fields as $field) {
            $element = $this->format($field, $item);
            $node->appendChild($element);
        }
    }

    /**
     * Format field.
     *
     * @param array    $field A field instance
     * @param FeedItem $item  An entity instance
     *
     * @return XDOMElement
     */
    protected function format($field, FeedItem $item)
    {
        $name = $field['name'];
        $method = $field['method'];
        $value = $item->{$method}();

        if (isset($field['cdata'])) {
            $value = $this->dom->createCDATASection($value);

            $element = $this->dom->createElement($name);
            $element->appendChild($value);
        } elseif (isset($field['attribute'])) {
            $element = $this->dom->createElement($name);
            $element->setAttribute($field['attribute'], $item->getLink());
        } else {
            if (isset($field['date_format'])) {
                $format = $field['date_format'];
                if (!$value instanceof \DateTime) {
                    throw new \InvalidArgumentException(sprintf('Field "%s" should be a DateTime instance.', $name));
                }

                $value = $value->format($format);
            }

            $element = $this->dom->createElement($name, $value);
        }

        return $element;
    }
}
