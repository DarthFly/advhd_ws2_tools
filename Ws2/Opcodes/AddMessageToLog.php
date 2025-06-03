<?php
namespace Ws2\Opcodes;

/**
 * 11 [string=message] 00
 */
class AddMessageToLog extends AbstractMessage
{
    public const OPCODE = '18';
    public const FUNC = 'AddMessageToLog';

    public function decompile(\Helper\FastBuffer &$dataSource): self
    {
        $config = $this->reader->readData($dataSource, 1);
        [$message, $length] = $this->reader->readString($dataSource);
        $this->compiledSize = 1 + 1 + $length;

        $this->textExtractor?->setMessage($message);
        $return = static::FUNC . " ({$config[0]}\n{$message}\n);";
        $this->content = $return;
        return $this;
    }

    public function preCompile(?string $params = null, ?array &$scriptLines = [], int &$messageIdOverride = 0): self
    {
        $message = $this->readMessage($scriptLines);
        $params = $this->reader->unpackParams($params . ')');
        $code = $this->reader->convertHexToChar(static::OPCODE) .
            pack('c', ...$params) .
            $this->reader->packString($message);
        $this->content = $code;
        return $this;
    }
}
