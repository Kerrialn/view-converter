<?php

namespace ViewConverter\Command;

use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use ViewConverter\Printer\Twig\TwigPrinter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class TwigConverterCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('php-to-twig')
            ->setDescription('Converts a PHP template file to Twig')
            ->addArgument('input', InputArgument::REQUIRED, 'PHP template file path');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getArgument('input');

        $finder = new Finder();
        $files = $finder->in($path)->name('*.php')->files();

        foreach ($files as $file) {
            $filename = $file->getFilenameWithoutExtension();
            $io->info("checking file: {$filename}");
            $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.0'));
            $fileContent = $file->getContents();
            $stmts = $parser->parse($fileContent);

            // 1. Check it's a template
            // 1a. check if it has any html tags
            // 1b. check if there are a lot of open and close tags
            if (!$this->isPhpViewTemplate($fileContent)) {
                continue;
            }

            // 2. Convert template
            $printer = new TwigPrinter();
            $twigCode = $printer->print($stmts);

            // 3. print file
            $outputPath = str_replace('.php', '.twig', $file->getPathname());
            file_put_contents($outputPath, $twigCode);
            $output->writeln("Generated Twig: " . $outputPath);

            // 4. output diff

        }

        return Command::SUCCESS;
    }

    private function isPhpViewTemplate(string $code): bool
    {
        return preg_match('/<\\/?(html|div|span|head|body|p|section|h[1-6])\\b/i', $code)
            || substr_count($code, '<?php') > 1;
    }

}
