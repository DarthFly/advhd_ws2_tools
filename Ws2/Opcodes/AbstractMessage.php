<?php
namespace Ws2\Opcodes;

use Ws2\FilesValidator;

abstract class AbstractMessage extends AbstractOpcode
{
    protected function readMessage(array &$scriptLines = []): string
    {
        $message = '';
        $textLine = array_shift($scriptLines);
        while($textLine !== ');') {
            $message .= $textLine;
            $textLine = array_shift($scriptLines);
        }
        return $message;
    }

    public function validate(?string $params, array &$dataSource, FilesValidator $filesValidator): ?string
    {
        // Skip some lines
        $textLine = array_shift($dataSource);
        while($textLine !== ');') {
            $textLine = array_shift($dataSource);
        }
        return null;
    }
}