<?php

/*
 * This file is part of the AllProgrammic ResqueBunde package.
 *
 * (c) AllProgrammic SAS <contact@allprogrammic.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AllProgrammic\Bundle\ResqueBundle\Pagination;

class Paginator
{
    /**
     * @var array
     */
    private $adapter;

    /**
     * @var integer
     */
    private $count;

    /**
     * @var array
     */
    private $currentResults;

    /**
     * @var integer
     */
    private $maxPerPage;

    /**
     * @var integer
     */
    private $currentPage;

    /**
     * Paginator constructor.
     *
     * @param $adapter
     */
    public function __construct($adapter)
    {
        $this->adapter = $adapter;
        $this->count   = $adapter->count();
    }

    /**
     * @param $value
     */
    public function setMaxPerPage($value)
    {
        $this->maxPerPage = $value;

        return $this;
    }

    public function setCurrentPage($value)
    {
        $this->currentPage = $value;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @return int
     */
    public function getMaxPerPage()
    {
        return $this->maxPerPage;
    }

    /**
     * @return float|int
     */
    public function getCurrentFrom()
    {
        return ($this->currentPage - 1) * $this->maxPerPage;
    }

    /**
     * @return int
     */
    public function getCurrentCount()
    {
        return $this->getCurrentFrom() + count($this->getCurrentResults());
    }

    /**
     * @param array $currentResults
     */
    public function setCurrentResults(array $currentResults)
    {
        $this->currentResults = $currentResults;
    }

    /**
     * @return array
     */
    public function getCurrentResults()
    {
        return $this->currentResults;
    }

    /**
     * @return bool
     */
    public function hasPrev()
    {
        return $this->currentPage > 1;
    }

    /**
     * @return bool
     */
    public function hasNext()
    {
        return ($this->currentPage * $this->maxPerPage) < $this->count;
    }

    /**
     * @return array
     */
    public function getCurrentPageResults()
    {
        $this->setCurrentResults($this->adapter->peek($this->getCurrentFrom(), $this->maxPerPage));

        return $this->getCurrentResults();
    }
}