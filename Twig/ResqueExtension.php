<?php

namespace AllProgrammic\Bundle\ResqueBundle\Twig;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

class ResqueExtension extends \Twig_Extension
{
    private $redisDsn;

    private $redisPrefix;

    private $cloner;

    public function __construct($redisDsn, $redisPrefix)
    {
        $this->redisDsn = $redisDsn;
        $this->redisPrefix = $redisPrefix;
        $this->cloner = new VarCloner();
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('resque_server', [$this, 'getServer']),
            new \Twig_SimpleFunction('resque_namespace', [$this, 'getNamespace']),
        ];
    }
    
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'resque_inspect',
                [$this, 'inspect'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function getServer()
    {
        return $this->redisDsn;
    }

    public function getNamespace()
    {
        return rtrim($this->redisPrefix, ':');
    }

    public function inspect($value)
    {
        $dump = fopen('php://memory', 'r+b');

        $dumper = new HtmlDumper($dump);
        $dumper->setStyles([
            'default' => 'background-color:#18171B; color:#FF8400; line-height:1.2em; font:12px Menlo, Monaco, Consolas, monospace; word-wrap: break-word; white-space: pre-wrap; position:relative; z-index:0; word-break: normal',
        ]);
        $dumper->dump($this->cloner->cloneVar($value));

        rewind($dump);
        return stream_get_contents($dump);
    }
}
