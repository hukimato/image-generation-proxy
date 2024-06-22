<?php
/**
 * This class has been auto-generated by PHP-DI.
 */
class CompiledContainer extends DI\CompiledContainer{
    const METHOD_MAPPING = array (
  'App\\Domain\\Image\\ImageStorageInterface' => 'get1',
  'App\\Application\\Queue\\QueueInterface' => 'get2',
  'App\\Infrastructure\\ImageGenerator\\BaseImageGenerator' => 'get3',
  'Doctrine\\ORM\\EntityManagerInterface' => 'get4',
  'Psr\\Log\\LoggerInterface' => 'get5',
  'App\\Application\\Settings\\SettingsInterface' => 'get6',
);

    protected function get1()
    {
        return $this->resolveFactory(static function (\Psr\Container\ContainerInterface $c) {
            $settings = $c->get(\App\Application\Settings\SettingsInterface::class);

            return new \App\Infrastructure\Storage\ImageStorage(
                $c->get(\Doctrine\ORM\EntityManagerInterface::class),
                $c->get(\App\Application\Queue\QueueInterface::class),
            );
        }, 'App\\Domain\\Image\\ImageStorageInterface');
    }

    protected function get2()
    {
        return $this->resolveFactory(static function (\Psr\Container\ContainerInterface $c) {
            $settings = $c->get(\App\Application\Settings\SettingsInterface::class);
            $rabbit = $settings->get('rabbit');

            return new \App\Infrastructure\Queue\RabbitQueue(
                $rabbit['queue'],
                new \PhpAmqpLib\Connection\AMQPStreamConnection($rabbit['host'], $rabbit['port'], $rabbit['user'], $rabbit['pass'])
            );
        }, 'App\\Application\\Queue\\QueueInterface');
    }

    protected function get3()
    {
        return $this->resolveFactory(static function (\Psr\Container\ContainerInterface $container) {
            $settings = $container->get(\App\Application\Settings\SettingsInterface::class);
            $generationStrategy = $settings->get('generationStrategy');
            $yandexSettings = $settings->get('yandexArt');

            $publicPath = '/www/mysite.local/app' . '/../public';
            switch ($generationStrategy) {
                case 'mock':
                default:
                    return new \App\Infrastructure\ImageGenerator\MockGenerator($publicPath, '/static/images/mock');
                case 'yandexArt':
                    return new \App\Infrastructure\ImageGenerator\YandexArtGenerator(
                        $publicPath,
                        '/static/images/generated',
                        $yandexSettings['folder_id'],
                        $yandexSettings['yandex_oauth_token'],
                    );
            }
        }, 'App\\Infrastructure\\ImageGenerator\\BaseImageGenerator');
    }

    protected function get4()
    {
        return $this->resolveFactory(static function (\Psr\Container\ContainerInterface $c) {
            $settings = $c->get(\App\Application\Settings\SettingsInterface::class);

            $config = \Doctrine\ORM\ORMSetup::createAttributeMetadataConfiguration(
                paths: [
                    '/www/mysite.local/app' . "/../src/Domain"
                ],
                isDevMode: true,
            );

            $connection = \Doctrine\DBAL\DriverManager::getConnection($settings->get('db'), $config);

            $em = new \Doctrine\ORM\EntityManager($connection, $config);

            return $em;
        }, 'Doctrine\\ORM\\EntityManagerInterface');
    }

    protected function get5()
    {
        return $this->resolveFactory(static function (\Psr\Container\ContainerInterface $c) {
            $settings = $c->get(\App\Application\Settings\SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new \Monolog\Logger($loggerSettings['name']);

            $processor = new \Monolog\Processor\UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new \Monolog\Handler\StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        }, 'Psr\\Log\\LoggerInterface');
    }

    protected function get6()
    {
        return $this->resolveFactory(static function () {
            $env = \getenv('ENV') ?? 'production';

            return new \App\Application\Settings\Settings([
                'env' => $env,

                'displayErrorDetails' => $env !== 'production', // Should be set to false in production
                'logError'            => false,
                'logErrorDetails'     => false,
                'logger' => [
                    'name' => 'slim-app',
                    'path' => isset($_ENV['docker']) ? 'php://stdout' : '/www/mysite.local/app' . '/../logs/app.log',
                    'level' => \Monolog\Logger::DEBUG,
                ],
                'db' => [
                    'driver' => 'pdo_pgsql',
                    'user' => \getenv('POSTGRES_USER'),
                    'password' => \getenv('POSTGRES_PASS'),
                    'dbname' => \getenv('POSTGRES_DBNAME'),
                    'host' => \getenv('POSTGRES_HOST'),
                    'port' =>  (int) \getenv('POSTGRES_POST'),
                ],
                'rabbit' => [
                    'user' => \getenv('RABBITMQ_USER'),
                    'pass' => \getenv('RABBITMQ_PASS'),
                    'queue' => \getenv('RABBITMQ_QUEUE'),
                    'host' => \getenv('RABBITMQ_HOST'),
                    'port' => (int) \getenv('RABBITMQ_PORT'),
                ],
                'generationStrategy' => \getenv('GENERATION_STRATEGY'),
                'yandexArt' => [
                    'folder_id' => \getenv('FOLDER_ID'),
                    'yandex_oauth_token' => \getenv('YANDEX_OAUTH_TOKEN'),
                ]
            ]);
        }, 'App\\Application\\Settings\\SettingsInterface');
    }

}
