<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Episode;
use App\Entity\Language;
use App\Entity\Media;
use App\Entity\Movie;
use App\Entity\Playlist;
use App\Entity\PlaylistMedia;
use App\Entity\PlaylistSubscription;
use App\Entity\Season;
use App\Entity\Serie;
use App\Entity\Subscription;
use App\Entity\SubscriptionHistory;
use App\Entity\User;
use App\Enum\CommentStatusEnum;
use App\Enum\UserStatusEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public const MAX_USERS = 8;
    public const MAX_MEDIA = 80;
    public const MAX_SUBSCRIPTIONS = 5;
    public const MAX_SEASONS = 5;
    public const MAX_EPISODES = 15;

    public const PLAYLISTS_PER_USER = 2;
    public const MAX_MEDIA_PER_PLAYLIST = 4;
    public const MAX_LANGUAGE_PER_MEDIA = 4;
    public const MAX_CATEGORY_PER_MEDIA = 2;
    public const MAX_SUBSCRIPTIONS_HISTORY_PER_USER = 5;
    public const MAX_COMMENTS_PER_MEDIA = 7;
    public const MAX_PLAYLIST_SUBSCRIPTION_PER_USERS = 2;

    public function load(ObjectManager $manager): void
    {
        $users = [];
        $medias = [];
        $playlists = [];
        $categories = [];
        $languages = [];
        $subscriptions = [];

        $this->createUsers($manager, $users);
        $this->createPlaylists($manager, $users, $playlists);
        $this->createSubscriptions($manager, $users, $subscriptions);
        $this->createCategories($manager, $categories);
        $this->createLanguages($manager, $languages);
        $this->createMedia($manager, $medias);
        $this->createComments($manager, $medias, $users);

        $this->linkMediaToPlaylists($medias, $playlists, $manager);
        $this->linkSubscriptionToUsers($users, $subscriptions, $manager);
        $this->linkMediaToCategories($medias, $categories);
        $this->linkMediaToLanguages($medias, $languages);

        $this->addUserPlaylistSubscriptions($manager, $users, $playlists);

        $manager->flush();
    

    }

    protected function createSubscriptions(ObjectManager $manager, array &$users, array &$subscriptions): void
    {
        $array = [
            ['name' => 'Free', 'duration' => 1, 'price' => 0, 'description' => 'Free subscription'],
            ['name' => 'Basic', 'duration' => 3, 'price' => 5, 'description' => 'Basic subscription'],
            ['name' => 'Standard', 'duration' => 6, 'price' => 10, 'description' => 'Standard subscription'],
            ['name' => 'Premium', 'duration' => 12, 'price' => 15, 'description' => 'Premium subscription'],
            ['name' => 'VIP', 'duration' => 24, 'price' => 20, 'description' => 'VIP subscription'],
        ];

        foreach ($array as $item) {
            $subscription = new Subscription();
            $subscription->setName($item['name']);
            $subscription->setDurationInMonths($item['duration']);
            $subscription->setPrice($item['price']);
            $subscription->setDescription($item['description']);

            $manager->persist($subscription);
            $subscriptions[] = $subscription;

            for ($i = 0; $i < random_int(1, self::MAX_SUBSCRIPTIONS); $i++) {
                $randomUser = $users[array_rand($users)];
                $randomUser->setCurrentSubscription(currentSubscription: $subscription);
            }
        }
    }

    protected function createMedia(ObjectManager $manager, array &$medias): void
    {
        for ($j = 0; $j < self::MAX_MEDIA; $j++) {
            $media = random_int(min: 0, max: 1) === 0 ? new Movie() : new Serie();
            $title = $media instanceof Movie ? 'Movie ' : 'Serie ';

            $media->setTitle(title: "{$title} n°{$j}");
            $media->setShortDescription(shortDescription: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. {$j}');
            $media->setLongDescription(longDescription: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. {$j}');
            $media->setReleaseDate(releaseDate: new \DateTime());
            $media->setCoverImage(coverImage: 'https://picsum.photos/200/300?random={$j}');
            $manager->persist(object: $media);
            $medias[] = $media;

            $this->addCastingAndStaff($media);
            if ($media instanceof Serie) {
                $this->addSeasons($media, $manager);
            }

            if ($media instanceof Movie) {
                //$media->setDuration(duration: random_int(60, 180));
            }
        }
    }

    protected function addSeasons(Serie $media, ObjectManager $manager): void
    {
        for ($i = 0; $i < random_int(1, self::MAX_SEASONS); $i++) {
            $season = new Season();
            $season->setSeasonNumber(seasonNumber: $i + 1);
            $season->setSerie(serie: $media);
            $manager->persist(object: $season);
            $this->createEpisodes($season, $manager);
        }
    }

    protected function createUsers(ObjectManager $manager, array &$users): void
    {
        for ($i = 0; $i < self::MAX_USERS; $i++) {
            $user = new User();
            $user->setEmail(email: "test_{$i}@example.com");
            $user->setFirstName(firstName: "test_{$i}");
            $user->setLastName(lastName: "test_{$i}");
            $user->setPassword(password: 'motdepasse');
            $user->setAccountStatus(UserStatusEnum::ACTIVE);
            $users[] = $user;

            $manager->persist(object: $user);
        }
    }

    protected function createPlaylists(ObjectManager $manager, array $users, array &$playlists): void
    {
        foreach ($users as $user) {
            for ($i = 0; $i < self::PLAYLISTS_PER_USER; $i++) {
                $playlist = new Playlist();
                $playlist->setName(name: "Playlist {$i} for {$user->getFirstName()}");
                $playlist->setCreatedAt(createdAt: new \DateTimeImmutable());
                $playlist->setUpdatedAt(updatedAt: new \DateTimeImmutable());
                $playlist->setCreatedBy(createdBy: $user);
                $playlists[] = $playlist;

                $manager->persist(object: $playlist);
            }
        }
    }

    protected function createCategories(ObjectManager $manager, array &$categories): void
    {
        $array = [
            ['name' => 'Action', 'label' => 'Action'],
            ['name' => 'Adventure', 'label' => 'Adventure'],
            ['name' => 'Comedy', 'label' => 'Comedy'],
            ['name' => 'Drama', 'label' => 'Drama'],
            ['name' => 'Fantasy', 'label' => 'Fantasy'],
            ['name' => 'Horror', 'label' => 'Horror'],
            ['name' => 'Mystery', 'label' => 'Mystery'],
            ['name' => 'Romance', 'label' => 'Romance'],
            ['name' => 'Science Fiction', 'label' => 'Science Fiction'],
            ['name' => 'Thriller', 'label' => 'Thriller'],
        ];

        foreach ($array as $item) {
            $category = new Category();
            $category->setName(name: $item['name']);
            $category->setLabel(label: $item['label']);
            $manager->persist(object: $category);
            $categories[] = $category;
        }
    }

    protected function createLanguages(ObjectManager $manager, array &$languages): void
    {
        $array = [
            ['name' => 'English', 'code' => 'en'],
            ['name' => 'French', 'code' => 'fr'],
            ['name' => 'German', 'code' => 'de'],
            ['name' => 'Italian', 'code' => 'it'],
            ['name' => 'Spanish', 'code' => 'es'],
            ['name' => 'Portuguese', 'code' => 'pt'],
            ['name' => 'Russian', 'code' => 'ru'],
            ['name' => 'Chinese', 'code' => 'zh'],
            ['name' => 'Japanese', 'code' => 'ja'],
            ['name' => 'Korean', 'code' => 'ko'],
        ];

        foreach ($array as $item) {
            $language = new Language();
            $language->setName(name: $item['name']);
            $language->setCode(code: $item['code']);
            $manager->persist(object: $language);
            $languages[] = $language;
        }
    }

    protected function createSeasons(ObjectManager $manager, Serie $media): void
    {
        for ($i = 0; $i < self::MAX_SEASONS; $i++) {
            $season = new Season();
            $season->setSeasonNumber(seasonNumber: $i + 1);
            $season->setSerie(serie: $media);
            $manager->persist(object: $season);
            $this->createEpisodes($season, $manager);
        }
    }    

    protected function createEpisodes(Season $season, ObjectManager $manager): void
    {
        for ($i = 0; $i < self::MAX_EPISODES; $i++) {
            $episode = new Episode();
            $episode->setSeason(season: $season);
            $episode->setTitle('Episode ' . ($i + 1));
            $episode->setDuration((new \DateTimeImmutable())->setTimestamp(random_int(10, 60)));
            $episode->setReleaseDate(new \DateTimeImmutable());
            $manager->persist($episode);
        }
    }

    protected function createComments(ObjectManager $manager, array $medias, array $users): void
    {
        /** @var Media $media */
        foreach ($medias as $media) {
            for ($i = 0; $i < self::MAX_COMMENTS_PER_MEDIA; $i++) {
                $comment = new Comment();
                $comment->setContent("Lorem ipsum dolor sit amet, consectetur adipiscing elit.{$i}");
                $comment->setStatus(random_int(0, 1) === 1 ? CommentStatusEnum::VALID : CommentStatusEnum::WAITING);
                $comment->setWrittenBy($users[array_rand($users)]);
                $comment->setMedia($media);

                $manager->persist($comment);
            }
        }
    }  
    
    

    // link methods
    protected function linkMediaToCategories(array $medias, array $categories): void
    {
        foreach ($medias as $media) {
            for ($i = 0; $i < random_int(1, self::MAX_CATEGORY_PER_MEDIA); $i++) {
                $media->addCategory($categories[array_rand($categories)]);
            }
        }
    }

    protected function linkMediaToLanguages(array $medias, array $languages): void
    {
        foreach ($medias as $media) {
            for ($i = 0; $i < random_int(1, self::MAX_LANGUAGE_PER_MEDIA); $i++) {
                $media->addLanguage($languages[array_rand($languages)]);
            }
        }
    }

    protected function linkMediaToPlaylists(array $medias, array $playlists, ObjectManager $manager): void
    {
        foreach ($playlists as $playlist) {
            for ($i = 0; $i < random_int(1, self::MAX_MEDIA_PER_PLAYLIST); $i++) {
                $playlistMedia = new PlaylistMedia();
                $playlistMedia->setPlaylist($playlist);
                $playlistMedia->setMedia($medias[array_rand($medias)]);
                $playlistMedia->setAddedAt(new \DateTimeImmutable());
                $manager->persist($playlistMedia);
            }
        }
    }

    protected function linkSubscriptionToUsers(array $users, array $subscriptions, ObjectManager $manager): void
    {
        foreach ($users as $user) {
            $user->setCurrentSubscription($subscriptions[array_rand($subscriptions)]);
        }
    }

    protected function addUserPlaylistSubscriptions(ObjectManager $manager, array $users, array $playlists): void
    {
        foreach ($users as $user) {
            for ($i = 0; $i < self::MAX_PLAYLIST_SUBSCRIPTION_PER_USERS; $i++) {
                $playlistSubscription = new PlaylistSubscription();
                $playlistSubscription->setUser($user);
                $playlistSubscription->setPlaylist($playlists[array_rand($playlists)]);
                $playlistSubscription->setSubscribedAt(new \DateTimeImmutable());
                $manager->persist($playlistSubscription);
            }
        }
    }

    protected function addCastingAndStaff(Media $media)
    {
        $staffData = [
            ['name' => 'John Doe', 'role' => 'Producteur', 'image' => 'https://i.pravatar.cc/150?u=John+Doe'],
            ['name' => 'Jane Doe', 'role' => 'Scénariste', 'image' => 'https://i.pravatar.cc/150?u=Jane+Doe'],
            ['name' => 'Foo Bar', 'role' => 'Compositeur', 'image' => 'https://i.pravatar.cc/150?u=Foo+Bar'],
            ['name' => 'Baz Qux', 'role' => 'Directeur de la photographie', 'image' => 'https://i.pravatar.cc/150?u=Baz+Qux'],
            ['name' => 'Alice Bob', 'role' => 'Monteur', 'image' => 'https://i.pravatar.cc/150?u=Alice+Bob'],
            ['name' => 'Charlie Delta', 'role' => 'Costumier', 'image' => 'https://i.pravatar.cc/150?u=Charlie+Delta'],
            ['name' => 'Eve Fox', 'role' => 'Maquilleur', 'image' => 'https://i.pravatar.cc/150?u=Eve+Fox'],
            ['name' => 'Grace Hope', 'role' => 'Ingénieur du son', 'image' => 'https://i.pravatar.cc/150?u=Grace+Hope'],
            ['name' => 'Ivy Jack', 'role' => 'Coordinateur des cascades', 'image' => 'https://i.pravatar.cc/150?u=Ivy+Jack'],
        ];

        $castingData = [
            ['name' => 'John Doe', 'role' => 'Acteur', 'image' => 'https://i.pravatar.cc/150?u=John+Doe'],
            ['name' => 'Jane Doe', 'role' => 'Actrice', 'image' => 'https://i.pravatar.cc/150?u=Jane+Doe'],
            ['name' => 'Foo Bar', 'role' => 'Acteur', 'image' => 'https://i.pravatar.cc/150?u=Foo+Bar'],
            ['name' => 'Baz Qux', 'role' => 'Acteur', 'image' => 'https://i.pravatar.cc/150?u=Baz+Qux'],
            ['name' => 'Alice Bob', 'role' => 'Actrice', 'image' => 'https://i.pravatar.cc/150?u=Alice+Bob'],
            ['name' => 'Charlie Delta', 'role' => 'Acteur', 'image' => 'https://i.pravatar.cc/150?u=Charlie+Delta'],
            ['name' => 'Eve Fox', 'role' => 'Actrice', 'image' => 'https://i.pravatar.cc/150?u=Eve+Fox'],
            ['name' => 'Grace Hope', 'role' => 'Acteur', 'image' => 'https://i.pravatar.cc/150?u=Grace+Hope'],
            ['name' => 'Ivy Jack', 'role' => 'Actrice', 'image' => 'https://i.pravatar.cc/150?u=Ivy+Jack'],
        ];

        $staff = [];
        for ($i = 0; $i < random_int(2, 5); $i++) {
            $staff[] = $staffData[array_rand($staffData)];
        }

        $media->setStaff($staff);

        $casting = [];
        for ($i = 0; $i < random_int(3, 5); $i++) {
            $casting[] = $castingData[array_rand($castingData)];
        }

        $media->setCasting($casting);
    }
}