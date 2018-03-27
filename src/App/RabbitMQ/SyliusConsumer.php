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

        $article->setTitle(sprintf('%s - %s' , $data['name'], $data['variantName']));
        $article->setStructureType('article_default');
        $article->getStructure()->bind(
            [
                'title' => $article->getTitle(),
                'productCode' => $data['code'],
                'productName' => $data['name'],
                'productPrice' => $data['price'],
                'variantCode' => $data['variantCode'],
                'variantName' => $data['variantName'],
            ]
        );

        $this->documentManager->persist($article, 'en');
        $this->documentManager->flush();
    }
}
