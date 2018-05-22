<?php
defined('BASEPATH') OR exit('No direct script access allowed');

requireClass(WebService::class, 'third_party/webclient/');
includeClass('*', 'third_party/query/');

/**
 * Proxy class for {@see WebService}.
 * @author Ray Naldo
 */
class MY_Web_service
{
    /** @var WebService */
    private $webService;


    public function __construct ($baseUrl)
    {
        $this->webService = new WebService($baseUrl);
    }

    protected function getEndpointUrl ($uri)
    {
        return $this->webService->getEndpointUrl($uri);
    }

    public function setHeader ($field, $value)
    {
        $this->webService->setHeader($field, $value);
        return $this;
    }

    public function attachUploadedFile ($name, $fileField)
    {
        $this->webService->attachUploadedFile($name, $fileField);
        return $this;
    }

    public function attachFile ($name, $filePath)
    {
        $this->webService->attachFile($name, $filePath);
        return $this;
    }

    public function addJsonFormData ($name, array $data)
    {
        $this->webService->addJsonFormData($name, $data);
        return $this;
    }

    /**
     * @param string $uri
     * @param GetParam $param
     * @return object response object: on success {success: true, data: object|array}, on failed {success: false, error: object}
     * on failed {success: false, error: object}
     */
    public function get ($uri = '', GetParam $param = null)
    {
        if (is_null($param))
            $param = GetParam::create();

        $filters = $param->getFilters();
        $fields = $param->getFields();
        $expands = $param->getExpands();
        $sorts = $param->getSorts();
        $limit = $param->getLimit();
        $offset = $param->getOffset();

        $params = array();
        if (!empty($filters))
        {
            foreach ($filters as $field => $value)
            {
                $key = sprintf('filters[%s]', $field);
                $params[$key] = $value;
            }
        }
        if (!empty($fields))
            $params['fields'] = implode(',', $fields);
        if (!empty($expands))
            $params['expands'] = implode(',', $expands);
        if (!empty($sorts))
            $params['sorts'] = implode(',', $sorts);
        if ($limit > 0)
            $params['limit'] = $limit;
        if ($offset > 0)
            $params['offset'] = $offset;

        return $this->webService->get($uri, $params);
    }

    public function search ($uri, SearchParam $param)
    {
        $condition = $param->getSearchCondition();
        $fields = $param->getFields();
        $expands = $param->getExpands();
        $sorts = $param->getSorts();
        $limit = $param->getLimit();
        $offset = $param->getOffset();

        $queryParams = array();
        if (!empty($fields))
            $queryParams['fields'] = implode(',', $fields);
        if (!empty($expands))
            $queryParams['expands'] = implode(',', $expands);
        if (!empty($sorts))
            $queryParams['sorts'] = implode(',', $sorts);
        if ($limit > 0)
            $queryParams['limit'] = $limit;
        if ($offset > 0)
            $queryParams['offset'] = $offset;

        if (!empty($queryParams))
        {
            $hasQueryParam = parse_url($uri, PHP_URL_QUERY);
            $uri .= ($hasQueryParam ? '&' : '?') . http_build_query($queryParams);
        }

        $searchParams = json_decode(json_encode($condition), true);
        return $this->post($uri, $searchParams);
    }

    /**
     * @param $uri
     * @param array|null $params
     * @return object on success {success: true, data: object|array}, on failed {success: false, error: object}
     */
    public function post ($uri = '', array $params = null)
    {
        return $this->webService->post($uri, $params);
    }

    /**
     * @param $uri
     * @param array|null $params
     * @return object on success {success: true, data: object|array}, on failed {success: false, error: object}
     */
    public function put ($uri = '', array $params = null)
    {
        return $this->webService->put($uri, $params);
    }

    /**
     * @param $uri
     * @param array|null $params
     * @return object on success {success: true, data: object|array}, on failed {success: false, error: object}
     */
    public function patch ($uri = '', array $params = null)
    {
        return $this->webService->patch($uri, $params);
    }


    /**
     * @param $uri
     * @param array|null $params
     * @return object on success {success: true, data: object|array}, on failed {success: false, error: object}
     */
    public function delete ($uri = '', array $params = null)
    {
        return $this->webService->delete($uri, $params);
    }
}