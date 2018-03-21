<?php

namespace App\RabbitMQ;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Sulu\Bundle\ArticleBundle\Document\ArticleDocument;
use Sulu\Component\DocumentManager\DocumentManagerInterface;

class SyliusConsumer implements ConsumerInterface
{
    /**
     * @var DocumentManagerInterface
     */
    private $documentManager;

    public function __construct(DocumentManagerInterface $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    public function execute(AMQPMessage $msg)
    {
        $data = unserialize($msg->getBody());

        /** @var ArticleDocument $article */
        $article = $this->documentManager->create('article');

        $article->setTitle($data['name']);
        $article->setStructureType('article_default');
        $article->getStructure()->bind(['title' => $data['name'], 'productCode' => $data['code']]);

        $this->documentManager->persist($article, 'en');
        $this->documentManager->flush();
    }
}
