<?php


namespace App\Modules\connector;


abstract class DbConnector
{
    private $serviceName;
    private $host;
    private $port;
    private $sid;
    private $db;
    private $user;
    private $password;

    protected function __construct(?string $serviceName, string $host, ?string $port, ?string $sid, string $db, string $user, string $password)
    {
        $this->serviceName = $serviceName;
        $this->host = $host;
        $this->port = $port;
        $this->sid = $sid;
        $this->db = $db;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * @return string|null
     */
    public function getServiceName(): ?string
    {
        return $this->serviceName;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return string|null
     */
    public function getPort(): ?string
    {
        return $this->port;
    }

    /**
     * @return string|null
     */
    public function getSid(): ?string
    {
        return $this->sid;
    }

    /**
     * @return string
     */
    public function getDb(): string
    {
        return $this->db;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    final private function __clone() {}
}
