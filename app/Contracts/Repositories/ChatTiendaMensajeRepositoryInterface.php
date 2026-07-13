<?php

namespace App\Contracts\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;

interface ChatTiendaMensajeRepositoryInterface extends RepositoryInterface
{
    /**
     * Page of messages for a chat, newest first. $offset is the page number.
     *
     * @param int $chatId
     * @param int $limit
     * @param int $offset
     * @return LengthAwarePaginator
     */
    public function getPageByChat(int $chatId, int $limit, int $offset): LengthAwarePaginator;

    /**
     * Mark as read every unread message of the chat NOT sent by the receiver.
     *
     * @param int $chatId
     * @param int $receptorId
     * @return int rows updated
     */
    public function markReceivedAsRead(int $chatId, int $receptorId): int;
}
