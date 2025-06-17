<?php
namespace Ws2\Opcodes;

use Exception;

/**
 * 58 [string=channel][NULL][string=effect_name][NULL]
 */
class Effect3 extends AbstractOpcode
{
    public const OPCODE = '58';
    public const FUNC = 'Effect3';

    public function decompile(\Helper\FastBuffer &$dataSource): self
    {
        [$channel, $channelLen] = $this->reader->readString($dataSource);
        [$effectName, $nameLen] = $this->reader->readString($dataSource);
        $this->compiledSize = 1 + $channelLen + $nameLen;

        $this->content = static::FUNC . " ({$channel}, {$effectName})";
        return $this;
    }

    public function preCompile(?string $params = null): self
    {
        $params = $this->reader->unpackParams($params);

        $this->content = $this->reader->convertHexToChar(static::OPCODE) .
            $this->reader->packString($params[0]) .
            $this->reader->packString($params[1]);
        return $this;
    }
}
