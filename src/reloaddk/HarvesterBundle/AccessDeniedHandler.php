<?php

namespace reloaddk\HarvesterBundle;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    /**
     * @var EngineInterface
     */
    protected $templating;

    public function __construct(EngineInterface $templating) {
        $this->templating = $templating;
    }

    /**
     * Render login page if access denied because of wrong role.
     *
     * @param Request $request
     * @param AccessDeniedException $accessDeniedException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    function handle(Request $request, AccessDeniedException $accessDeniedException) {
        return $this->templating->renderResponse('reloaddkHarvesterBundle:Admin:login.html.twig', [
            'last_username' => $request->getSession()->get(SecurityContext::LAST_USERNAME),
            'error'         => ['message' => "You don't have permission to access this area"]
        ]);
    }
}