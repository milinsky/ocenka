<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'app:test-email',
    description: 'Test email sending',
)]
final class TestEmailCommand extends Command
{
    public function __construct(
        private readonly MailerInterface $mailer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('🔍 Testing email configuration...');
        $output->writeln('MAILER_DSN: ' . ($_ENV['MAILER_DSN'] ?? 'NOT SET'));
        $output->writeln('');
        
        $output->writeln('📧 Sending test email...');

        $email = (new Email())
            ->from('test@ocenka.local')
            ->to('admin@example.com')
            ->subject('Test Email from Ocenka - ' . date('Y-m-d H:i:s'))
            ->text('This is a test email to verify MailHog is working!' . "\n\nTimestamp: " . date('Y-m-d H:i:s'));

        try {
            $this->mailer->send($email);
            $output->writeln('<info>✅ Email sent successfully via Mailer!</info>');
            $output->writeln('');
            $output->writeln('📬 Check MailHog at: http://localhost:8025');
            $output->writeln('⏱️  Timestamp: ' . date('Y-m-d H:i:s'));
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>❌ Failed to send email:</error>');
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            $output->writeln('');
            $output->writeln('Stack trace:');
            $output->writeln($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}

