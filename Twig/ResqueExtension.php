<?php

namespace AllProgrammic\Bundle\ResqueBundle\Twig;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

class ResqueExtension extends \Twig_Extension
{
    private $cloner;

    public function __construct()
    {
        $this->cloner = new VarCloner();
    }


    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'inspect',
                [$this, 'inspect'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function inspect($value)
    {
        $dump = fopen('php://memory', 'r+b');

        $dumper = new HtmlDumper($dump);
        $dumper->dump($this->cloner->cloneVar($value));

        rewind($dump);
        return stream_get_contents($dump);
    }
}
