<?php
declare(strict_types=1);

namespace App\Option;

use Illuminate\Support\Facades\Config;

class ResourceItem extends Response
{
    public function create()
    {
        $get = new \App\Option\Method\GetRequest();
        $this->verbs['GET'] = $get->setParameters(Config::get('api.resource.parameters.item'))->
            setAuthenticationStatus($this->permissions['view'])->
            setDescription('route-descriptions.resource_GET_show')->
            option();

        $delete = new \App\Option\Method\DeleteRequest();
        $this->verbs['DELETE'] = $delete->setDescription('route-descriptions.resource_DELETE')->
            setAuthenticationRequirement(true)->
            setAuthenticationStatus($this->permissions['manage'])->
            option();

        $patch = new \App\Option\Method\PatchRequest();
        $this->verbs['PATCH'] = $patch->setFields(Config::get('api.resource.fields'))->
            setDescription('route-descriptions.resource_PATCH')->
            setAuthenticationRequirement(true)->
            setAuthenticationStatus($this->permissions['manage'])->
            option();

        return $this;
    }
}