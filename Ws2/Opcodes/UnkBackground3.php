<?php
namespace Ws2\Opcodes;

use Exception;

/**
 * 41 [string=channel][NULL]
 * [byte]
 */
class UnkBackground3 extends AbstractOpcode
{
    public const OPCODE = '41';
    public const FUNC = 'UnkBackground3';

    public function decompile(\Helper\FastBuffer &$dataSource): self
    {
        // @todo test KAN_2002.ws2
        [$channel, $channelLen] = $this->reader->readString($dataSource);
        $configBytes = $this->reader->readData($dataSource, 1);
        $this->compiledSize += 1 + $channelLen + 1;

        $this->content = static::FUNC . " ({$channel}, {$configBytes[0]})";
        return $this;
    }

    public function preCompile(?string $params = null): self
    {
        $params = $this->reader->unpackParams($params);
        $channel = array_shift($params);

        $this->content = $this->reader->convertHexToChar(static::OPCODE) .
            $this->reader->packString($channel) .
            $this->reader->packArray($params, 'c', 1, 'intval');
        return $this;
    }
}
