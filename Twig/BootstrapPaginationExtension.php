<?php

namespace Dontdrinkandroot\UtilsBundle\Twig;

use Dontdrinkandroot\Pagination\Pagination;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Extension;
use Twig_SimpleFunction;

class BootstrapPaginationExtension extends Twig_Extension
{
    private $generator;

    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ddr_bootstrap_pagination';
    }

    public function getFunctions()
    {
        return array(
            'ddr_pagination' => new Twig_SimpleFunction(
                'ddr_pagination',
                [$this, 'generatePagination'],
                ['is_safe' => ['html']]
            )
        );
    }

    /**
     * @param Pagination $pagination
     * @param Request    $request
     *
     * @return string
     */
    public function generatePagination($pagination, Request $request)
    {
        $route = $request->attributes->get('_route');
        $params = array_merge($request->attributes->get('_route_params'), $request->query->all());

        $html = '<ul class="pagination">' . "\n";

        /* Render prev page */
        $cssClasses = [];
        if ($pagination->getCurrentPage() == 1) {
            $cssClasses[] = 'disabled';
        }
        $cssClasses[] = 'page-item';
        $html .= $this->renderLink($pagination->getCurrentPage() - 1, '&laquo;', $route, $params, $cssClasses, 'prev');

        $surroundingStartIdx = max(1, $pagination->getCurrentPage() - 2);
        $surroundingEndIdx = min($pagination->getTotalPages(), $pagination->getCurrentPage() + 2);

        /* Render first page */
        if ($surroundingStartIdx > 1) {
            $html .= $this->renderLink(1, 1, $route, $params);
        }

        /* Render dots */
        if ($surroundingStartIdx > 2) {
            $html .= '<li class="page-item disabled"><a class="page-link" href="#">&hellip;</a></li>' . "\n";
        }

        /* Render surrounding pages */
        if ($pagination->getTotalPages() > 0) {
            for ($i = $surroundingStartIdx; $i <= $surroundingEndIdx; $i++) {
                $cssClasses = [];
                $cssClasses[] = 'page-item';
                if ($i == $pagination->getCurrentPage()) {
                    $cssClasses[] = 'active';
                }
                $html .= $this->renderLink($i, $i, $route, $params, $cssClasses);
            }
        }

        /* Render dots */
        if ($surroundingEndIdx < $pagination->getTotalPages() - 1) {
            $html .= '<li class="page-item disabled"><a class="page-link" href="#">&hellip;</a></li>' . "\n";
        }

        /* Render last page */
        if ($surroundingEndIdx < $pagination->getTotalPages()) {
            $html .= $this->renderLink($pagination->getTotalPages(), $pagination->getTotalPages(), $route, $params);
        }

        /* Render next page */
        $cssClasses = array();
        if ($pagination->getCurrentPage() >= $pagination->getTotalPages()) {
            $cssClasses[] = 'disabled';
        }
        $html .= $this->renderLink($pagination->getCurrentPage() + 1, '&raquo;', $route, $params, $cssClasses, 'next');

        $html .= '</ul>' . "\n";

        return $html;
    }

    public function renderLink($page, $text, $route, $params, $cssClasses = [], $rel = null)
    {
        $params['page'] = $page;
        $path = '#';
        if (!in_array('disabled', $cssClasses)) {
            $path = $this->getPath($route, $params);
        }

        $html = '<li class="' . implode(' ', $cssClasses) . '">';
        $html .= '<a class=\'page-link\' href="' . $path . '"';
        if (null !== $rel) {
            $html .= ' rel="' . $rel . '"';
        }
        $html .= '>' . $text . '</a>';
        $html .= '</li>' . "\n";

        return $html;
    }

    public function getPath($name, $parameters = array(), $relative = false)
    {
        return $this->generator->generate(
            $name,
            $parameters,
            $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH
        );
    }
}
