<?php

namespace WMC\SwiftmailerTwigBundle\Mailer;

use Swift_Mailer;
use Swift_Message;
use Swift_Image;
use Symfony\Component\DomCrawler\Crawler;
use Twig_Environment;

/**
 * Inspired from FOS User Bundle
 * @link https://github.com/FriendsOfSymfony/FOSUserBundle/blob/90cb9c1785f7a7c406dddc5295b41e85b6c65905/Mailer/TwigSwiftMailer.php
 */
class TwigSwiftHelper
{
    /**
     * @var Twig_Environment
     */
    protected $twig;

    /**
     * Used to resolve inline images
     * @var string absolute path to Web root
     */
    protected $web_directory;

    public function __construct(Twig_Environment $twig, $web_directory)
    {
        $this->twig          = $twig;
        $this->web_directory = $web_directory;
    }

    protected function extractMessageContent($template_name, $context, $parts = array('subject', 'content' => 'body_text'))
    {
        $template = $this->twig->loadTemplate($template_name);
        $context = $this->twig->mergeGlobals($context);

        $data = array();

        foreach ($parts as $k => $part) {
            $key = is_numeric($k) ? $part : $k;
            $data[$key] = $template->renderBlock($part, $context);
        }

        return $data;
    }

    /**
     * Replace all src of img.inline-image with an embedded image
     *
     * @param  Swift_Message $message
     */
    protected function inlineImages(Swift_Message $message)
    {
        $html = $message->getBody();

        $crawler = new Crawler();
        $crawler->addHtmlContent($html);

        $imgs = array();
        $replaces = array();

        foreach ($crawler->filterXPath("//img[contains(concat(' ',normalize-space(@class), ' '), ' inline-image ')]") as $img) {
            $normalized_src = $src = $img->getAttribute('src');

            if (isset($replaces['src="'.$src.'"'])) {
               continue;
            }

            // if starting with one slash, use local file
            if (preg_match('#^/[^/]#', $normalized_src)) {
                $normalized_src = $this->web_directory . parse_url($src, PHP_URL_PATH);
            }

            if (!isset($imgs[$normalized_src])) {
                $swift_image = Swift_Image::fromPath($normalized_src);
                $imgs[$normalized_src] = $message->embed($swift_image);
            }

            $replaces['src=\''.$src.'\''] = 'src="'.$imgs[$normalized_src].'"';
            $replaces['src="' .$src.'"' ] = 'src="'.$imgs[$normalized_src].'"';
        }

        if (count($replaces)) {
            $html = str_replace(array_keys($replaces), array_values($replaces), $html);
            $message->setBody($html);
        }
    }

    public function populateMessage(Swift_Message $message, $template_name, $context)
    {
        $data = $this->extractMessageContent($template_name, $context, array('subject', 'text' => 'body_text', 'html' => 'body_html'));

        if (empty($data['subject']) || empty($data['text'])) {
            throw new \InvalidArgumentException('The mail template must have (non-empty) subject ('.$data['subject'].') and body_text ('.$data['text'].') blocks');
        }

        $message->setSubject($data['subject']);

        if (!empty($data['html'])) {
            $message->setBody($data['html'], 'text/html')
                    ->addPart($data['text'], 'text/plain');

            $this->inlineImages($message);
        } else {
            $message->setBody($data['text']);
        }

        return $message;
    }
}
