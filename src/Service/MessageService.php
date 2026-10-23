<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class MessageService
{
    public function __construct(private RequestStack $requestStack) {}
    
    public function addSuccess(string $message): void {
        $this->requestStack->getSession()->getFlashBag()->add('success', $message);
    }
    
    public function addError(string $message): void {
        $this->requestStack->getSession()->getFlashBag()->add('error', $message);
    }
    
    public function addWarning(string $message): void {
        $this->requestStack->getSession()->getFlashBag()->add('warning', $message);
    }
}