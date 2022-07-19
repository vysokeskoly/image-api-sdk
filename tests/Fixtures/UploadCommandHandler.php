<?php declare(strict_types=1);

namespace VysokeSkoly\ImageApi\Sdk\Fixtures;

use Lmc\Cqrs\Types\Base\AbstractSendCommandHandler;
use Lmc\Cqrs\Types\CommandInterface;
use Lmc\Cqrs\Types\ValueObject\OnErrorInterface;
use Lmc\Cqrs\Types\ValueObject\OnSuccessInterface;
use VysokeSkoly\ImageApi\Sdk\Command\UploadImageCommand;

/** @phpstan-extends AbstractSendCommandHandler<mixed, array> */
class UploadCommandHandler extends AbstractSendCommandHandler
{
    public const SUCCESS_RESPONSE = [
        'status' => 'OK',
        'isSuccess' => true,
        'messages' => [],
    ];

    public function supports(CommandInterface $command): bool
    {
        return $command instanceof UploadImageCommand;
    }

    /** @param UploadImageCommand $command */
    public function handle(CommandInterface $command, OnSuccessInterface $onSuccess, OnErrorInterface $onError): void
    {
        if (!$this->assertIsSupported(UploadImageCommand::class, $command, $onError)) {
            return;
        }

        $response = self::SUCCESS_RESPONSE;
        $response['messages'][] = $command->getImageHash()->getHash();

        $onSuccess($response);
    }
}
