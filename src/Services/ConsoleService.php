<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Services;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;


class ConsoleService
{
    private const COLORS = [
        'black',
        'red',
        'green',
        'yellow',
        'blue',
        'magenta',
        'cyan',
        'white',
        'default',
        'gray',
        'bright-red',
        'bright-green',
        'bright-yellow',
        'bright-blue',
        'bright-magenta',
        'bright-cyan',
        'bright-white',
    ];

    public function __construct(private readonly ConsoleOutput $output) {
        foreach (self::COLORS as $color) {
            $this->output->getFormatter()->setStyle($color, new OutputFormatterStyle($color));
        }
    }

    public function concatenate(...$messages): ?string
    {
        $result = count($messages) === 0
            ? null
            : '';

        foreach ($messages as $message) {
            $result .= $message;
        }

        return $result;
    }

    public function write(string $message): void
    {
        $this->output->write($message);
    }

    public function writeln(string $message): void
    {
        $this->output->writeln($message);
    }

    public function newline(): string
    {
        return "\n";
    }

    public function space(): string
    {
        return ' ';
    }

    public function black(string $message): string
    {
        return "<black>$message</black>";
    }

    public function red(string $message): string
    {
        return "<red>$message</red>";
    }

    public function green(string $message): string
    {
        return "<green>$message</green>";
    }

    public function yellow(string $message): string
    {
        return "<yellow>$message</yellow>";
    }

    public function blue(string $message): string
    {
        return "<blue>$message</blue>";
    }

    public function magenta(string $message): string
    {
        return "<magenta>$message</magenta>";
    }

    public function cyan(string $message): string
    {
        return "<cyan>$message</cyan>";
    }

    public function white(string $message): string
    {
        return "<white>$message</white>";
    }

    public function default(string $message): string
    {
        return "<default>$message</default>";
    }

    public function gray(string $message): string
    {
        return "<gray>$message</gray>";
    }

    public function brightRed(string $message): string
    {
        return "<bright-red>$message</bright-red>";
    }

    public function brightGreen(string $message): string
    {
        return "<bright-green>$message</bright-green>";
    }

    public function brightYellow(string $message): string
    {
        return "<bright-yellow>$message</bright-yellow>";
    }

    public function brightBlue(string $message): string
    {
        return "<bright-blue>$message</bright-blue>";
    }

    public function brightMagenta(string $message): string
    {
        return "<bright-magenta>$message</bright-magenta>";
    }

    public function brightCyan(string $message): string
    {
        return "<bright-cyan>$message</bright-cyan>";
    }

    public function brightWhite(string $message): string
    {
        return "<bright-white>$message</bright-white>";
    }

}
