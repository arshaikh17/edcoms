<?php

namespace EdcomsCMS\ContentBundle\Helpers;

/**
 * This helper class is used to handle the Quiz actions
 *
 * @author richard
 */
class QuizHelper extends APIHelper {
    private $id;
    private $user;
    private $config;
    
    public function setQuiz($id)
    {
        $this->id = $id;
    }
    
    public function getQuiz()
    {
        return $this->id;
    }
    
    public function setUser($user)
    {
        if ($user !== 'anon.') {
            $this->user = $user;
        }
        return $this;
    }
    
    public function setConfig($config)
    {
        $this->config = $config;
    }
    public function getUser()
    {
        return $this->user;
    }
    public function launchQuiz($type='', $token='', $additional=[])
    {
        $em = $this->getDoctrine()->getManager('edcoms_cms');
        $connectors = $em->getRepository('EdcomsCMSAuthBundle:Connector');
        $connector = $connectors->findOneBy(['site'=>'quiz']);
        
        if ($connector) {
            $hook = $connector->getDefaultHook($type);
            $key = $connector->getKeys()->first();
            if ($type === 'teacher') {
                $url = $hook->getUrl().'?'. http_build_query(array_merge([
                    'userid'=>$this->user->getId(),
                    'signature'=> base64_encode($this->signData([$this->user->getUsername()], $key->getPrivateKey())),
                    'siteid'=>$this->config['site_id'],
                    'quizid'=>$this->getQuiz()
                ], $additional));
            } else if ($type === 'student') {
                $url = $hook->getUrl().'?'. http_build_query(array_merge([
                    'instance_token'=>$token,
                    'userid'=>$this->user->getId(),
                    'signature'=> base64_encode($this->signData([$this->user->getUsername()], $key->getPrivateKey())),
                    'siteid'=>$this->config['site_id']
                ], $additional));
            }
            return $url;
        }
    }
}
