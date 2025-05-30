<?php
namespace Ws2\Opcodes;

/**
 * 3B [string=channel] 00 [string=message] 00
 * [int16][int32][float x 8]
 */
class BackgroundMessage extends AbstractMessage
{
    public const OPCODE = '3B';
    public const FUNC = 'BackgroundMessage';

    public function decompile(\Helper\FastBuffer &$dataSource): self
    {
        [$channel, $length] = $this->reader->readString($dataSource);
        [$message, $messageLen] = $this->reader->readString($dataSource);
        $messageId = $this->reader->readWord($dataSource);
        $int = $this->reader->readDWord($dataSource);
        $floats = $this->reader->readFloats($dataSource, 8);
        $this->compiledSize = 1 + $length + $messageLen + 2 + 4 + 8 * 4;

        $this->textExtractor?->setMessage($message);
        $return = static::FUNC . " ($channel, $messageId, $int, ".implode(', ', $floats)."\n{$message}\n);";
        $this->content = $return;
        return $this;
    }

    public function preCompile(?string $params = null, ?array &$scriptLines = [], int &$messageIdOverride = 0): self
    {
        $message = $this->readMessage($scriptLines);
        $params = $this->reader->unpackParams($params . ')');
        $channel = $params[0];
        $messageId = (int)$params[1];
        $int = (int)$params[2];
        unset($params[0], $params[1], $params[2]);
        foreach ($params as $key => $value) {
            $params[$key] = (float)$value;
        }
        $code = $this->reader->convertHexToChar(static::OPCODE) .
            $this->reader->packString($channel) .
            $this->reader->packString($message) .
            pack('vVf8', $messageId, $int, ...$params);
        $this->content = $code;
        return $this;
    }
}
