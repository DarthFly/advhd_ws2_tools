<?php
namespace Ws2;

use Exception;

class Reader
{
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
        $result = $dataSource->readString();
        $stringLen = $dataSource->offset - $initialOffset; // Calculate length based on consumed bytes
        return [$result, $stringLen];
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
        $len = strlen($text);
        return pack('a' . $len, $text) . chr(00);
    }

    public function packArray(array &$params, string $type, int $count, string $function): string
    {
        $data = array_splice($params, 0, $count);
        $data = array_map($function, $data);
        return pack($type . $count, ...$data);
    }
}
