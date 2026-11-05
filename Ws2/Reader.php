<?php
namespace Ws2;

use Exception;

class Reader
{
    private ?string $stringMethod = null;

    public function __construct(string $encoding)
    {
        $encoding = ucfirst(strtolower($encoding));
        if ($encoding && !method_exists($this, 'getString'.$encoding)) {
            echo "Encoding {$encoding} is not supported. Either use uft16 or nothing.";
            exit();
        }
        $this->stringMethod = 'getString'.$encoding;
    }

    public function getHex(int $code): string
    {
        $hex = strtoupper(dechex($code));
        return str_pad($hex, 2, '0', STR_PAD_LEFT);
    }

    public function convertHexToChar(string $hex): string
    {
        $int = hexdec($hex);
        return chr($int);
    }

    /**
     * @throws Exception
     */
    public function readData(\Helper\FastBuffer &$dataSource, int $size): ?array
    {
        if ($size < 1) {
            return null;
        }
        $result = [];
        while ($size > 0) {
            if (empty($dataSource)) {
                throw new Exception("Unable to read data - incorrect size {$size} for code.");
            }
            $result[] = $dataSource->shift();
            $size --;
        }
        return $result;
    }

    public function readString(\Helper\FastBuffer &$dataSource): array
    {
        $initialOffset = $dataSource->offset; // Store initial offset to calculate length
        $result = $this->{$this->stringMethod}($dataSource);
        $stringLen = $dataSource->offset - $initialOffset; // Calculate length based on consumed bytes
        return [$result, $stringLen];
    }

    /** One byte string */
    public function getString(\Helper\FastBuffer &$dataSource): string
    {
        return $dataSource->readString();
    }

    public function getStringUtf16(\Helper\FastBuffer &$dataSource): string
    {
        return $dataSource->read2ByteString();
    }

    public function readFloat(\Helper\FastBuffer &$dataSource): float
    {
        $result = unpack('f', $dataSource->readFixedLengthString(4));
        return $result[1];
    }

    public function readDWord(\Helper\FastBuffer &$dataSource): int
    {
        $result = unpack('V', $dataSource->readFixedLengthString(4));
        return $result[1];
    }

    public function readWord(\Helper\FastBuffer &$dataSource): int
    {
        $result = unpack('v', $dataSource->readFixedLengthString(2));
        return $result[1];
    }

    public function readFloats(\Helper\FastBuffer &$dataSource, int $max): array
    {
        $result = [];
        for ($i = 0; $i < $max; $i ++) {
            $result[] = $this->readFloat($dataSource);
        }
        return $result;
    }

    public function readDWords(\Helper\FastBuffer &$dataSource, int $max): array
    {
        $result = [];
        for ($i = 0; $i < $max; $i ++) {
            $result[] = $this->readDWord($dataSource);
        }
        return $result;
    }

    public function unpackParams(?string $params): array
    {
        if ($params === '') {
            return [];
        }
        // Remove brackets
        $params = substr($params, 1, -1);
        return explode(', ', $params);
    }

    public function packString(string $text): string
    {
        $end =  chr(00);
        if ($this->stringMethod != 'getString') {
            $text = mb_convert_encoding($text,  'UTF-16LE','UTF-8');
            $end = chr(00) . chr(00);
        }
        $len = strlen($text);
        return pack('a' . $len, $text) . $end;
    }

    public function packArray(array &$params, string $type, int $count, string $function): string
    {
        $data = array_splice($params, 0, $count);
        $data = array_map($function, $data);
        return pack($type . $count, ...$data);
    }
}
