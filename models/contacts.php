<?php

class contactsModel extends Fw_Model_Base
{
    public function saveMessage($data)
    {
        $this->sql->setQuery("
            INSERT INTO contacts_feedback (
                cf_name,
                cf_email,
                cf_subject,
                cf_text
            ) VALUES (
                '" . $data['name'] . "',
                '" . $data['email'] . "',
                '" . $data['subject'] . "',
                '" . htmlentities($data['text'], ENT_QUOTES, 'UTF-8') . "'
            )
        ");
        $this->sql->sendQuery();
        return;
    }
}
