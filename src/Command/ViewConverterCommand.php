<?php

namespace ViewConverter\Command;

use PhpParser\ParserFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use ViewConverter\Converter\PhpToBladeConverter;
use ViewConverter\Printer\Twig\TwigPrinter;

class ViewConverterCommand extends Command
{
    private const FORMAT_TWIG = 'twig';

    private const FORMAT_BLADE = 'blade';

    protected function configure(): void
    {
        $this->setName('convert')
            ->setDescription('Converts raw PHP templates to Twig or Blade')
            ->addArgument('input', InputArgument::REQUIRED, 'Template file or directory path')
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Output format: twig or blade')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Preview conversion without writing files')
            ->addOption('delete-originals', null, InputOption::VALUE_NONE, 'Delete original files after conversion');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getArgument('input');
        $dryRun = (bool) $input->getOption('dry-run');
        $deleteOriginals = (bool) $input->getOption('delete-originals');

        if (! file_exists($path)) {
            $io->error("Path not found: $path");
            return Command::FAILURE;
        }

        $format = $this->resolveFormat($input, $output);
        if ($format === null) {
            $io->error('Unknown format. Use --format=twig or --format=blade.');
            return Command::FAILURE;
        }

        if (is_file($path)) {
            $this->processFile($path, $format, $dryRun, $deleteOriginals, $io);
            return Command::SUCCESS;
        }

        $finder = (new Finder())->in($path)->name('*.php')->notName('*.blade.php')->files();
        $files = iterator_to_array($finder, false);

        if (empty($files)) {
            $io->warning("No *.php files found in $path");
            return Command::SUCCESS;
        }

        foreach ($files as $file) {
            $this->processFile($file->getPathname(), $format, $dryRun, $deleteOriginals, $io);
        }

        return Command::SUCCESS;
    }

    private function resolveFormat(InputInterface $input, OutputInterface $output): ?string
    {
        $format = $input->getOption('format');

        if ($format !== null) {
            return in_array($format, [self::FORMAT_TWIG, self::FORMAT_BLADE], true) ? $format : null;
        }

        $question = new ChoiceQuestion(
            'Which format would you like to convert TO?',
            [
                self::FORMAT_TWIG => 'Twig (*.twig)',
                self::FORMAT_BLADE => 'Blade (*.blade.php)',
            ],
            self::FORMAT_TWIG
        );

        return $this->getHelper('question')->ask($input, $output, $question);
    }

    private function processFile(string $filePath, string $format, bool $dryRun, bool $deleteOriginals, SymfonyStyle $io): void
    {
        $io->info('Converting: ' . basename($filePath));

        $content = $format === self::FORMAT_BLADE
            ? $this->convertToBlade($filePath, $io)
            : $this->convertToTwig($filePath, $io);

        if ($content === null) {
            return;
        }

        $outputPath = $format === self::FORMAT_BLADE
            ? preg_replace('/\.php$/', '.blade.php', $filePath)
            : preg_replace('/\.php$/', '.twig', $filePath);

        if ($dryRun) {
            $io->writeln('--- ' . basename($filePath) . ' → ' . basename($outputPath) . ' ---');
            $io->writeln($content);
            $io->writeln('');
        } else {
            file_put_contents($outputPath, $content);
            $io->writeln("Generated: $outputPath");

            if ($deleteOriginals) {
                unlink($filePath);
                $io->writeln("Deleted:   $filePath");
            }
        }
    }

    private function convertToBlade(string $filePath, SymfonyStyle $io): ?string
    {
        $content = file_get_contents($filePath);

        if (! $this->isPhpViewTemplate($content)) {
            $io->note('Skipped (not a view template): ' . basename($filePath));
            return null;
        }

        return (new PhpToBladeConverter())->convert($content);
    }

    private function convertToTwig(string $filePath, SymfonyStyle $io): ?string
    {
        $content = file_get_contents($filePath);

        if (! $this->isPhpViewTemplate($content)) {
            $io->note('Skipped (not a view template): ' . basename($filePath));
            return null;
        }

        $stmts = (new ParserFactory())->createForHostVersion()->parse($content);
        if ($stmts === null) {
            $io->warning("Could not parse: $filePath");
            return null;
        }

        return (new TwigPrinter())->print($stmts);
    }

    private function isPhpViewTemplate(string $code): bool
    {
        return (bool) preg_match(
            '/<\/?(html|head|body|div|span|p|a|ul|ol|li|table|tr|td|th|thead|tbody|tfoot|form|input|button|select|option|textarea|label|nav|header|footer|main|section|article|aside|h[1-6]|img|link|script|style)\b/i',
            $code
        ) || substr_count($code, '<?php') > 1;
    }
}
