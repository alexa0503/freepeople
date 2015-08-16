<?php
namespace AppBundle\Controller;

use AppBundle\Wechat\Wechat;
use Imagine\Gd\Imagine;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Helper;
use AppBundle\Entity;
use Symfony\Component\Validator\Constraints\DateTime;

#use Symfony\Component\Validator\Constraints\Image;

class DefaultController extends Controller
{
    public function getUser()
    {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:WechatUser')->findOneByOpenId($session->get('open_id'));
        return $user;
    }
    /**
     * @Route("/", name="_index")
     */
    public function indexAction()
    {
        return $this->render('AppBundle:default:index.html.twig');
    }
    /**
     * @Route("/test/", name="_test")
     */
    public function testAction()
    {
        return $this->render('AppBundle:default:test.html.twig');
    }
    /**
     * @Route("/res/{t}", name="_res")
     */
    public function resAction(Request $request, $t = 1)
    {
        if( !in_array($t, array(1,2,3)))
            $t = 1;
        $session = $request->getSession();
        $session->set('resType', $t);
        return $this->render('AppBundle:default:res.html.twig',array(
            't'=>$t,
            'wx_share_success_url' => $this->generateUrl('_share', array('t'=>$t)),
        ));
    }
    /**
     * @Route("/share/{t}", name="_share")
     */
    public function shareAction(Request $request, $t = 1)
    {
        if( !in_array($t, array(1,2,3)))
            $t = 1;
        return $this->render('AppBundle:default:share.html.twig',array('t'=>$t));
    }
     /**
     * @Route("/info/", name="_info")
     */
    public function infoAction(Request $request)
    {
        $session = $request->getSession();
        return $this->render('AppBundle:default:info.html.twig');
    }
    /**
     * @Route("/post/", name="_post")
     */
    public function postAction(Request $request)
    {
        $session = $request->getSession();
        $res = array(
            'ret' => 0,
            'msg' => '',
            'url' => $this->generateUrl('_success'),
        );
        $t = $session->get('resType');
        $username = $request->get('name');
        $email = $request->get('email');
        if( null == $t){
            $res['ret'] = 1001;
            $res['msg'] = '来源不正确';
        }
        elseif( $username == $t){
            $res['ret'] = 1002;
            $res['msg'] = '请输入用户名';
        }
        elseif( $email == $t){
            $res['ret'] = 1003;
            $res['msg'] = '请输入Email';
        }
        else{
            try {
                $em = $this->getDoctrine()->getManager();
                $user = $this->getUser();
                $code = $em->getRepository('AppBundle:ExchangeCode')->findOneBy( array('isUsed'=>0));
                $form = new Entity\Form();
                $form->setUsername($username);
                $form->setEmail($email);
                $form->setType($t);
                $form->setCreateIp($request->getClientIp());
                $form->setCreateTime(new \DateTime('now'));
                $form->setUser($user);
                $form->setCode($code);
                $code->setIsUsed(1);
                $em->persist($form);
                $em->persist($code);
                $em->flush();
                $session->set('resType',null);
            } catch (Exception $e) {
                $res['ret'] = 1101;
                $res['msg'] = $e->getMessage();
            }
        }
        return new Response(json_encode($res));
    }
     /**
     * @Route("/success/{id}", name="_success")
     * @Route("/success/", name="_success_")
     */
    public function successAction(Request $request, $id = 0)
    {
        $user = $this->getUser();
        $form = $this->getDoctrine()->getRepository('AppBundle:Form')->find($id);
        if( null == $form || $user->getId() != $form->getUser()->getId())
            return $this->redirect($this->generateUrl('_index'));

        return $this->render('AppBundle:default:success.html.twig', array('form'=>$form));
    }
    /**
     * @Route("callback/", name="_callback")
     */
    public function callbackAction(Request $request)
    {
        $session = $request->getSession();
        $code = $request->query->get('code');
        //$state = $request->query->get('state');
        $app_id = $this->container->getParameter('wechat_appid');
        $secret = $this->container->getParameter('wechat_secret');
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $app_id . "&secret=" . $secret . "&code=$code&grant_type=authorization_code";
        $data = Helper\HttpClient::get($url);
        $token = json_decode($data);
        //$session->set('open_id', null);
        if ( isset($token->errcode) && $token->errcode != 0) {
            return new Response('something bad !');
        }

        $wechat_token = $token->access_token;
        $wechat_openid = $token->openid;
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$wechat_token}&openid={$wechat_openid}";
        $data = Helper\HttpClient::get($url);
        $user_data = json_decode($data);

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try{
            $session->set('open_id', $user_data->openid);
            $repo = $em->getRepository('AppBundle:WechatUser');
            $qb = $repo->createQueryBuilder('a');
            $qb->select('COUNT(a)');
            $qb->where('a.openId = :openId');
            $qb->setParameter('openId', $user_data->openid);
            $count = $qb->getQuery()->getSingleScalarResult();
            if($count <= 0){
                $wechat_user = new Entity\WechatUser();
                $wechat_user->setOpenId($wechat_openid);
                $wechat_user->setNickName($user_data->nickname);
                $wechat_user->setCity($user_data->city);
                $wechat_user->setGender($user_data->sex);
                $wechat_user->setProvince($user_data->province);
                $wechat_user->setCountry($user_data->country);
                $wechat_user->setHeadImg($user_data->headimgurl);
                $wechat_user->setCreateIp($request->getClientIp());
                $wechat_user->setCreateTime(new \DateTime('now'));
                $em->persist($wechat_user);
                $em->flush();
            }
            else{
                $wechat_user = $em->getRepository('AppBundle:WechatUser')->findOneBy(array('openId' => $wechat_openid));
                $wechat_user->setHeadImg($user_data->headimgurl);
                $em->persist($wechat_user);
                $em->flush();
                $session->set('user_id', $wechat_user->getId());
            }

            $redirect_url = $session->get('redirect_url') == null ? $this->generateUrl('_index') : $session->get('redirect_url');
            $em->getConnection()->commit();
            return $this->redirect($redirect_url);
        }
        catch (Exception $e) {
            $em->getConnection()->rollback();
            return new Response($e->getMessage());
        }
    }
}
