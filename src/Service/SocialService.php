<?php

namespace App\Service;

use App\Service\ValidationService;

use App\Entity\User;
use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\Follow;
use App\Entity\FriendRequest;
use App\Entity\Like;
use App\Entity\Message;
use App\Entity\Notification;
use App\Entity\SavedPost;
use App\Entity\Story;
use Doctrine\ORM\EntityManagerInterface;

class SocialService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidationService $validator
    ) {}

    // -------------------------
    // USER
    // -------------------------
    public function createUser(array $data): User
    {
        $user = new User();
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setPassword($data['password']);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    // -------------------------
    // COMMENT
    // -------------------------
    public function createComment(array $data): Comment
    {
        $comment = new Comment();
        $comment->setContent($data['content']);
        $comment->setCreatedAt(new \DateTimeImmutable());

        $author = $this->em->getRepository(User::class)->find($data['author_id']);
        if (!$author) throw new \Exception("Author not found");
        $comment->setAuthor($author);

        $post = $this->em->getRepository(Post::class)->find($data['post_id']);
        if (!$post) throw new \Exception("Post not found");
        $comment->setPost($post);

        $this->em->persist($comment);
        $this->em->flush();

        return $comment;
    }

    // -------------------------
    // FOLLOW
    // -------------------------
    public function createFollow(array $data): Follow
    {
        $follow = new Follow();

        $follower = $this->em->getRepository(User::class)->find($data['follower_id']);
        if (!$follower) throw new \Exception("Follower not found");
        $follow->setFollower($follower);

        $following = $this->em->getRepository(User::class)->find($data['following_id']);
        if (!$following) throw new \Exception("Following user not found");
        $follow->setFollowing($following);

        $follow->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($follow);
        $this->em->flush();

        return $follow;
    }

    // -------------------------
    // FRIEND REQUEST
    // -------------------------
    public function createFriendRequest(array $data): FriendRequest
    {
        $req = new FriendRequest();

        $from = $this->em->getRepository(User::class)->find($data['from_user_id']);
        if (!$from) throw new \Exception("Sender not found");
        $req->setFromUser($from);

        $to = $this->em->getRepository(User::class)->find($data['to_user_id']);
        if (!$to) throw new \Exception("Receiver not found");
        $req->setToUser($to);

        $req->setStatus($data['status'] ?? 'pending');
        $req->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($req);
        $this->em->flush();

        return $req;
    }

    // -------------------------
    // LIKE
    // -------------------------
    public function createLike(array $data): Like
    {
        $like = new Like();

        $user = $this->em->getRepository(User::class)->find($data['liked_by']);
        if (!$user) throw new \Exception("User not found");
        $like->setLikedBy($user);

        $post = $this->em->getRepository(Post::class)->find($data['post_id']);
        if (!$post) throw new \Exception("Post not found");
        $like->setPost($post);

        $like->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($like);
        $this->em->flush();

        return $like;
    }

    // -------------------------
    // MESSAGE
    // -------------------------
    public function createMessage(array $data): Message
    {
        $msg = new Message();

        $sender = $this->em->getRepository(User::class)->find($data['sender_id']);
        if (!$sender) throw new \Exception("Sender not found");
        $msg->setSender($sender);

        $receiver = $this->em->getRepository(User::class)->find($data['receiver_id']);
        if (!$receiver) throw new \Exception("Receiver not found");
        $msg->setReceiver($receiver);

        $msg->setContent($data['content']);
        $msg->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($msg);
        $this->em->flush();

        return $msg;
    }

    // -------------------------
    // NOTIFICATION
    // -------------------------
    public function createNotification(array $data): Notification
    {
        $n = new Notification();

        $recipient = $this->em->getRepository(User::class)->find($data['recipient_id']);
        if (!$recipient) throw new \Exception("Recipient not found");
        $n->setRecipient($recipient);

        $n->setType($data['type']);
        $n->setMessage($data['message']);
        $n->setIsRead($data['is_read'] ?? false);
        $n->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($n);
        $this->em->flush();

        return $n;
    }

    // -------------------------
    // POST
    // -------------------------
    public function createPost(array $data): Post
    {
        $post = new Post();

        $post->setTitle($data['title']);
        $post->setContent($data['content']);

        $author = $this->em->getRepository(User::class)->find($data['author_id']);
        if (!$author) throw new \Exception("Author not found");
        $post->setAuthor($author);

        $post->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($post);
        $this->em->flush();

        return $post;
    }

    // -------------------------
    // SAVED POST
    // -------------------------
    public function createSavedPost(array $data): SavedPost
    {
        $saved = new SavedPost();

        $user = $this->em->getRepository(User::class)->find($data['author_id']);
        if (!$user) throw new \Exception("User not found");
        $saved->setAuthor($user);

        $post = $this->em->getRepository(Post::class)->find($data['post_id']);
        if (!$post) throw new \Exception("Post not found");
        $saved->setPost($post);

        $saved->setSavedAt(new \DateTimeImmutable());

        $this->em->persist($saved);
        $this->em->flush();

        return $saved;
    }

    // -------------------------
    // STORY
    // -------------------------
    public function createStory(array $data): Story
    {
        $story = new Story();

        $author = $this->em->getRepository(User::class)->find($data['author_id']);
        if (!$author) throw new \Exception("User not found");
        $story->setAuthor($author);

        $story->setMediaUrl($data['mediaUrl']);
        $story->setCaption($data['caption'] ?? null);

        $story->setCreatedAt(new \DateTimeImmutable());
        $story->setExpiresAt((new \DateTimeImmutable())->modify('+24 hours'));

        $this->em->persist($story);
        $this->em->flush();

        return $story;
    }
}
