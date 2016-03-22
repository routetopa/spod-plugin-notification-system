<?php

class SPODNOTIFICATION_CTRL_Test extends OW_ActionController
{

    public function index()
    {
        $commentsParams = new BASE_CommentsParams('spodnotification', SPODTCHAT_BOL_Service::ENTITY_TYPE);
        $commentsParams->setEntityId(4096);
        $commentsParams->setDisplayType(BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST);
        $commentsParams->setCommentCountOnPage(5);
        $commentsParams->setOwnerId((OW::getUser()->getId()));
        $commentsParams->setAddComment(TRUE);
        $commentsParams->setWrapInBox(false);
        $commentsParams->setShowEmptyList(false);

        $commentsParams->level = 0;
        $commentsParams->nodeId = 0;

        SPODTCHAT_CMP_Comments::$numberOfNestedLevels = 1;

        $commentCmp = new SPODTCHAT_CMP_Comments($commentsParams);
        $this->addComponent('comments', $commentCmp);
    }

}