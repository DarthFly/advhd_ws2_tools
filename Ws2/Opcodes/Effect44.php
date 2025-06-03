<?php
namespace Ws2\Opcodes;

use Exception;

/**
 * 44 [string=channel][NULL][string=effect_name][NULL]
 * [byte]
 */
class Effect44 extends AbstractOpcode
{
    public const OPCODE = '44';
    public const FUNC = 'Effect44';

    public function decompile(\Helper\FastBuffer &$dataSource): self
    {
        [$channel, $channelLen] = $this->reader->readString($dataSource);
        [$effectName, $nameLen] = $this->reader->readString($dataSource);
        $config = $this->reader->readData($dataSource, 1);
        $this->compiledSize = 1 + $channelLen + $nameLen + 1;

        $this->content = static::FUNC . " ({$channel}, {$effectName}, ".
            "".implode(', ', $config).")";
        return $this;
    }

    public function preCompile(?string $params = null): self
    {
        $params = $this->reader->unpackParams($params);

        $this->content = $this->reader->convertHexToChar(static::OPCODE) .
            $this->reader->packString($params[0]) .
            $this->reader->packString($params[1]) .
            pack('c', (int)$params[2]);
        return $this;
    }
}