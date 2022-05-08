<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\Media;

use Doctrine\ORM\EntityManager;
use EdcomsCMS\ContentBundle\Entity\Media;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class MediaType extends TextType
{

    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $mediaObject = false;
        $isVideo = false;

        if($options['media_object']===true){
            $options['data_class'] = Media::class;
            $mediaObject = true;
        }

        if($options['video_only']===true){
            $options['attr'] = array_merge($options['attr'],['data-video'=> '1']);
            $isVideo = true;
        }

        parent::buildForm($builder, $options);

        $em = $this->em;

        $builder
            ->addModelTransformer(new CallbackTransformer(
            function ($value) use($em, $mediaObject){
                if(!is_object($value) && (!$value || !is_numeric($value))){
                    return null;
                }else{
                    if(is_object($value) && $value instanceof Media){
                        $media =  $value;
                    }else{
                        $media = $mediaObject ? $value : $em->getRepository(Media::class)->find($value);
                        if(!$media){
                            throw new TransformationFailedException('Media not exist');
                        }
                    }
                    return $media;
                }
            },
            function ($value) use($em, $mediaObject, $isVideo){
                if(!$value){
                    return $mediaObject ? null : '';
                }else {
                    $pathinfo = pathinfo($value);
                    $mediaPath = preg_replace("/(\/media\/view\/?)/i", '', $pathinfo['dirname']);
                    $where = array(
                        'path' => $mediaPath,
                        'title' => urldecode($pathinfo['basename'])
                    );
                    $repo = $em->getRepository(Media::class);
                    $media = $isVideo ? $repo->findOneBy([
                        'videoId' => (int) $pathinfo['filename'] ]) : $repo->findOneBy($where);
                    if (!$media) {
                        throw new TransformationFailedException('Media not exist');
                    }

                    return $mediaObject ? $media : $media->getId();
                }
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'attr'=> array("class"=>"custom-field-file-picker hidden", "data-file-url"=> ""),
            'video_only'=> false,
            'media_object' => false,
            'data_class' => null
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $type = $options['video_only']===true ? 3 : 0;
        $view->vars['baseDialogURL'] = sprintf('/cms/filemanager/dialog.php?type=%s&field_id=', $type);
        /** @var Media $media */

        $media = $options['media_object']===false && is_numeric($form->getData()) ? $this->em->getRepository(Media::class)->find($form->getData()) : $form->getData();

        if(!$media){
            $mediaValue = '';
        }elseif ($media->getVideoId()){
            $mediaValue = sprintf('%s.mp4',$media->getVideoId());
        }else{
            $mediaValue = sprintf('/media/view/%s',$media->getPath() ? sprintf('%s/%s',$media->getPath(),$media->getTitle()) : $media->getTitle());
        }
        $view->vars['mediaValue'] = $mediaValue;
        $view->vars['mediaTitle'] = $media ? $media->getTitle() : '';
    }

    public function getBlockPrefix()
    {
        return 'media';
    }
}