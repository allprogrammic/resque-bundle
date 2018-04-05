<?php

namespace AllProgrammic\Bundle\ResqueBundle\Twig;

use AllProgrammic\Bundle\ResqueBundle\Pagination\Paginator;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

class PaginationExtension extends \Twig_Extension
{
    /**
     * @var string
     */
    private $defaultView;

    /**
     * PaginationExtension constructor.
     *
     * @param $defaultView
     */
    public function __construct($defaultView)
    {
        $this->defaultView = $defaultView;
    }

    /**
     * @return array|\Twig_Function[]
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('pagination', [$this, 'render'], ['is_safe' => ['html'], 'needs_environment' => true]),
        ];
    }

    /**
     * @param \Twig_Environment $env
     * @param Paginator $pager
     * @param $routeName
     *
     * @return string
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function render(\Twig_Environment $env, Paginator $pager, $routeName)
    {
        return $env->render($this->defaultView, [
            'pager' => $pager,
            'routeName' => $routeName,
        ]);
    }
}
