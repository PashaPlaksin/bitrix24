<?php
require_once(__DIR__ . '/src/crest.php');

if (isset($_REQUEST['event']) && $_REQUEST['event'] === 'ONCRMACTIVITYADD') {
    $activityId = $_REQUEST['data']['FIELDS']['ID'];
    $activity = CRest::call('crm.activity.get', ['id' => $activityId]);
    if ($activity && isset($activity['result']['OWNER_ID'])) {
        $contactId = $activity['result']['OWNER_ID'];
        $ownerType = $activity['result']['OWNER_TYPE_ID'];

        if ($ownerType == 3) {
            $currentDate = date('Y-m-d H:i:s');
            $updateResult = CRest::call('crm.contact.update', [
                'id' => $contactId,
                'fields' => ['UF_CRM_1739729752' => $currentDate]
            ]);
        }
    }
}


