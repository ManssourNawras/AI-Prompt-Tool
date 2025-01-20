<?php


namespace App\Traits;


trait MediaQuery
{
    /**
     * @param $model
     * @param $attachments
     */
    public function addAttachments($model, $attachments)
    {
        if (is_array($attachments)) {
            foreach ($attachments as $attachment) {
                $model->addMedia($attachment)->toMediaCollection();
            }
        } else {
            $model->addMedia($attachments)->toMediaCollection();
        }
    }

    /**
     * @param $model
     */
    public function deleteAttachments($model)
    {
        $medias = $model->getMedia();
        foreach ($medias as $media) {
            $media->delete();
        }
    }


}
