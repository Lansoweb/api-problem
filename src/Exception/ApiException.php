<?php
namespace LosMiddleware\ApiProblem\Exception;

class ApiException extends \Exception
{
    /**
     * @var mixed
     */
    protected $extra;

    /**
     * @var array
     */
    protected $arrayMessage;

    /**
     * {@inheritDoc}
     * @see Exception::__construct()
     */
    public function __construct($message = null, $code = null, $previous = null, array $arrayMessage = [], $extra = null)
    {
        parent::__construct($message, $code, $previous);
        $this->arrayMessage = $arrayMessage;
        $this->extra = $extra;
    }

    /**
     * @return array
     */
    public function getArrayMessage()
    {
        return $this->arrayMessage;
    }

    /**
     * @return mixed
     */
    public function getExtra()
    {
        return $extra;
    }

}
