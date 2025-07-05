<?php

namespace Laradoc\Laradoc\Livewire;

use Livewire\Component;
use Laradoc\Laradoc\Services\DocumentationGenerator;

class DocumentationComponent extends Component
{
    public $module = 'overview';
    public $content = '';
    public $title = '';
    public $isEditing = false;
    public $editContent = '';
    public $modules = [];

    protected $listeners = ['refreshDocumentation', 'switchModule'];

    public function mount($module = 'overview')
    {
        $this->module = $module;
        $this->loadDocumentation();
        $this->modules = config('laradoc.documentation.modules');
    }

    public function loadDocumentation()
    {
        $documentation = app(DocumentationGenerator::class)->getDocumentation($this->module);
        
        if ($documentation) {
            $this->content = $documentation['content'];
            $this->title = $documentation['title'] ?? ucfirst($this->module);
        } else {
            $this->content = '# Documentation Not Found';
            $this->title = 'Not Found';
        }
    }

    public function switchModule($module)
    {
        $this->module = $module;
        $this->loadDocumentation();
        $this->isEditing = false;
    }

    public function startEditing()
    {
        $this->editContent = $this->content;
        $this->isEditing = true;
    }

    public function cancelEditing()
    {
        $this->isEditing = false;
        $this->editContent = '';
    }

    public function saveDocumentation()
    {
        try {
            $result = app(DocumentationGenerator::class)->updateDocumentation($this->module, $this->editContent);
            
            if ($result['success']) {
                $this->content = $this->editContent;
                $this->isEditing = false;
                $this->editContent = '';
                $this->dispatch('documentation-saved', ['message' => 'Documentation updated successfully!']);
            } else {
                $this->dispatch('documentation-error', ['message' => 'Failed to update documentation']);
            }
        } catch (\Exception $e) {
            $this->dispatch('documentation-error', ['message' => $e->getMessage()]);
        }
    }

    public function refreshDocumentation()
    {
        $this->loadDocumentation();
    }

    public function render()
    {
        return view('laradoc::livewire.documentation', [
            'content' => $this->content,
            'title' => $this->title,
            'module' => $this->module,
            'modules' => $this->modules,
            'isEditing' => $this->isEditing,
        ]);
    }
} 