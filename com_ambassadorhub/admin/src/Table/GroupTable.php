<?php

/**
 * @package     SRH.Administrator
 * @subpackage  com_ambassadorhub
 */

namespace SRH\Component\Ambassadorhub\Administrator\Table;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Table class for Groups.
 *
 * @since  0.1.0
 */
class GroupTable extends Table
{
    /**
     * Constructor.
     *
     * @param   DatabaseInterface     $db          Database connector object
     * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
     *
     * @since   0.1.0
     */
    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
        $this->setColumnAlias('published', 'state');

        parent::__construct('#__ambassadorhub_groups', 'id', $db, $dispatcher);
    }

    /**
     * Overloaded check method to auto-generate the alias and set timestamps.
     *
     * @return  boolean  True on success.
     *
     * @since   0.1.0
     */
    public function check(): bool
    {
        if (trim($this->name ?? '') === '') {
            $this->setError('A Group name is required.');

            return false;
        }

        if (trim($this->alias ?? '') === '') {
            $this->alias = $this->name;
        }

        $this->alias = \str_replace(' ', '-', trim($this->alias));
        $this->alias = \preg_replace('/[^A-Za-z0-9\-_]/', '', $this->alias);
        $this->alias = \strtolower($this->alias);

        if ($this->alias === '') {
            $this->alias = 'group-' . time();
        }

        $isNew = empty($this->id);

        if ($isNew && empty($this->created)) {
            $this->created = Factory::getDate()->toSql();
        }

        if (!$isNew) {
            $this->modified = Factory::getDate()->toSql();
        }

        return true;
    }
}
