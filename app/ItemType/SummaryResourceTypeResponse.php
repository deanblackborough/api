<?php

namespace App\ItemType;

use App\Request\Parameter;
use App\Response\Header;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

abstract class SummaryResourceTypeResponse
{
    protected int $resource_type_id;

    protected bool $permitted_user;

    protected ?int $user_id;

    protected array $parameters;

    protected array $decision_parameters = [];

    protected array $filter_parameters;

    protected array $search_parameters;

    protected Model $model;

    protected \App\Cache\Control $cache_control;

    protected \App\Cache\Summary $cache_summary;

    public function __construct(
        int $resource_type_id,
        bool $permitted_user = false,
        int $user_id = null
    )
    {
        $this->resource_type_id = $resource_type_id;

        $this->permitted_user = $permitted_user;
        $this->user_id = $user_id;
    }

    abstract public function response(): JsonResponse;

    protected function assignToCache(
        array $summary,
        array $collection,
        \App\Cache\Control $cache_control,
        \App\Cache\Summary $cache_summary
    ): \App\Cache\Summary
    {
        $headers = new Header();

        $headers
            ->addCacheControl($cache_control->visibility(), $cache_control->ttl())
            ->addETag($collection)
            ->addParameters(Parameter\Request::xHeader())
            ->addFilter(Parameter\Filter::xHeader())
            ->addSearch(Parameter\Search::xHeader());

        if (array_key_exists(0, $summary)) {
            if (array_key_exists('last_updated', $summary[0]) === true) {
                $headers->addLastUpdated($summary[0]['last_updated']);
            }
            if (array_key_exists('total_count', $summary[0]) === true) {
                $headers->addTotalCount((int)$summary[0]['total_count']);
            }
        }

        $cache_summary->create($collection, $headers->headers());
        $cache_control->putByKey(request()->getRequestUri(), $cache_summary->content());

        return $cache_summary;
    }

    abstract protected function removeDecisionParameters(): void;

    protected function fetchAllRequestParameters(ItemType $entity): void
    {
        $this->parameters = Parameter\Request::fetch(
            array_keys($entity->summaryResourceTypeRequestParameters()),
            $this->resource_type_id
        );

        $this->search_parameters = Parameter\Search::fetch(
            $entity->summaryResourceTypeSearchParameters()
        );

        $this->filter_parameters = Parameter\Filter::fetch(
            $entity->summaryResourceTypeFilterParameters()
        );
    }

    protected function setUpCache(): void
    {
        $this->cache_control = new \App\Cache\Control(
            $this->permitted_user,
            $this->user_id
        );
        $this->cache_control->setTtlOneWeek();

        $this->cache_summary = new \App\Cache\Summary();
        $this->cache_summary->setFromCache($this->cache_control->getByKey(request()->getRequestUri()));
    }
}
