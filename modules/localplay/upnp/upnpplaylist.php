<?php
/**
 *
 * LICENSE: GNU Affero General Public License, version 3 (AGPLv3)
 * Copyright 2001 - 2015 Ampache.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * UPnPPlaylist Class
 */
class UPnPPlaylist
{
    private $_deviceGUID = "";
    private $_songs;
    private $_current = 0;

    /*
     * Playlist is its own for each UPnP device
     */
    public function __construct($deviceGUID)
    {
        $this->_deviceGUID = $deviceGUID;
        $this->PlayListRead();
        if (! is_array($this->_songs)) {
            $this->Clear();
        }
    }

    public function Add($name, $link)
    {
        $this->_songs[] = array('name' => $name, 'link' => $link);
        $this->PlayListSave();
    }

    public function RemoveTrack($track)
    {
        unset($this->_songs[$track - 1]);
        $this->PlayListSave();
    }

    public function Clear()
    {
        $this->_songs   = array();
        $this->_current = 0;
        $this->PlayListSave();
    }

    public function AllItems()
    {
        return $this->_songs;
    }

    public function CurrentItem()
    {
        $item = $this->_songs[$this->_current];
        return $item;
    }

    public function CurrentPos()
    {
        return $this->_current;
    }

    public function Next()
    {
        if ($this->_current < count($this->_songs) - 1) {
            $this->_current++;
            $this->PlayListSave();
            return true;
        }
        return false;
    }

    public function NextItem()
    {
        if ($this->_current < count($this->_songs) - 1) {
            $nxt = $this->_current + 1;
            return $this->_songs[$nxt];
        }
        return null;
    }

    public function Prev()
    {
        if ($this->_current > 0) {
            $this->_current--;
            $this->PlayListSave();
            return true;
        }
        return false;
    }

    public function Skip($pos)
    {
        // note that pos is started from 1 not from zero
        if (($pos >= 1) && ($pos <= count($this->_songs))) {
            $this->_current = $pos - 1;
            $this->PlayListSave();
            return true;
        }
        return false;
    }

    private function PlayListRead()
    {
        $sid      = 'upnp_pls_' . $this->_deviceGUID;
        $pls_data = unserialize(Session::read($sid));

        $this->_songs   = $pls_data['upnp_playlist'];
        $this->_current = $pls_data['upnp_current'];
    }

    private function PlayListSave()
    {
        $sid      = 'upnp_pls_' . $this->_deviceGUID;
        $pls_data = serialize(array(
            'upnp_playlist' => $this->_songs,
            'upnp_current' => $this->_current
        ));
        if (! Session::exists('api', $sid)) {
            Session::create(array('type' => 'api', 'sid' => $sid, 'value' => $pls_data ));
        } else {
            Session::write($sid, $pls_data);
        }
    }
}

?>