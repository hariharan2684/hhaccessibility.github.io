<?php

class SigninApiTest extends TestCase
{
    /**
     * Tests a successful authentication
     *
     * @return void
     */
    public function testPost()
    {
        $data = ['email' => 'test', 'password' => 'password'];
        $response = $this->post('/signin', $data);
        $this->assertEquals(302, $response->getStatusCode());
    }

    private function checkProfileAfterSignin()
    {
        // Test that we can access the profile.
        $response = $this->get('/profile');
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'My Reviews') !== false);
        $this->assertTrue(strpos($content, 'Sign out') !== false);
        $this->assertTrue(strpos($content, 'Suggestions') !== false);
    }

    private function checkLocationSearchRadius()
    {
        LocationSearchRadiusTest::useDistance($this, 0.5);
        LocationSearchRadiusTest::useDistance($this, 0.3);
    }

    private function checkAddLocationFeature()
    {
        $response = $this->get('/location/management/add');
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'Add New Location') !== false);
        $this->assertTrue(strpos($content, '>Add<') !== false);
    }

    private function checkLocationsAddedByMe()
    {
        $response = $this->get('/location/management/my-locations');
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'Added Locations') !== false);
    }

    private function checkChangePasswordFeature()
    {
        $response = $this->get('/user/change-password');
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'Change Password') !== false);
        $this->assertTrue(strpos($content, 'Update Password') !== false);
    }

    private function checkSuggestions()
    {
        $response = $this->get('/suggestion-list');
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'Suggestions') !== false);
    }

    private function checkNameChangeFeature()
    {
        $response = $this->get('/profile/names');
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'First Name') !== false);
        $this->assertTrue(strpos($content, 'Last Name') !== false);
    }

    private function checkHomeAddressFeature()
    {
        $response = $this->get('/profile/home-address');
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'Country') !== false);
        $this->assertTrue(strpos($content, 'City') !== false);
    }

    private function checkLocationGroups()
    {
        $response = $this->get('/location-groups');
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'Location Groups') !== false);
    }

    private function checkUserReport($userId)
    {
        $response = $this->get('/user-report/' . $userId);
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'Basic Information') !== false);
        $this->assertTrue(strpos($content, 'Ratings') !== false);
    }

    private function getUserIdFromUsersHTML($users_page_content)
    {
        // Scrape a user id out of the HTML content.
        $token = 'href="/user-report/';
        $index = strpos($users_page_content, $token);
        // At least the current user should be in there.
        $this->assertTrue($index !== false);
        $id_str = substr($users_page_content, $index + strlen($token));
        $index = strpos($id_str, '"'); // find end of URL string.
        $id_str = substr($id_str, 0, $index);
        return $id_str;
    }

    private function checkUsers()
    {
        $response = $this->get('/users');
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'Users') !== false);
        $this->assertTrue(strpos($content, 'Email') !== false);
        $this->assertTrue(strpos($content, 'total user(') !== false);

        // Check an individual user's report.
        $user_id = $this->getUserIdFromUsersHTML($content);
        $this->checkUserReport($user_id);
    }

    private function checkLocationTaggingFeature()
    {
        $response = $this->get('/location-tagging');
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'Location Tagging') !== false);
        $this->assertTrue(strpos($content, 'Name') !== false);
        $this->assertTrue(strpos($content, 'Tags') !== false);
    }

    private function checkInternalDashboard()
    {
        $response = $this->get('/dashboard');
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertTrue(strpos($content, 'Internal Dashboard') !== false);
        $this->assertTrue(strpos($content, 'Location Categories') !== false);

        // Check other internal features.
        $this->checkLocationGroups();
        $this->checkUsers();
        $this->checkLocationTaggingFeature();
    }
    
    private function saveProfileInformation($overrides)
    {
        $data = [
            'uses_screen_reader' => true,
            'home_country_id' => 39,
            'home_region' => 'Ontario',
            'home_city' => 'Windsor',
            'first_name' => 'John',
            'last_name' => 'Smith',
            'location_search_text' => '',
            'search_radius_km' => 1
        ];
        if (isset($overrides['uses_screen_reader']) && $overrides['uses_screen_reader'] === false) {
            unset($overrides['uses_screen_reader']);
            unset($data['uses_screen_reader']);
        }
        // merge with the overrides.
        $data = array_merge($data, $overrides);
        $response = $this->post('/profile', $data);
        $this->assertEquals(302, $response->getStatusCode());
        $response = $this->get('/profile');
        $content = $response->getContent();
        return $content;
    }
    
    private function isUsingScreenReader()
    {
        $response = $this->get('/api/is-using-screen-reader');
        $this->assertEquals(200, $response->getStatusCode());
        return json_decode($response->getContent());
    }

    private function checkScreenReader()
    {
        $content = $this->saveProfileInformation(['uses_screen_reader' => false]);
        $this->assertTrue(strpos($content, 'name="uses_screen_reader" checked') === false);
        $this->assertFalse($this->isUsingScreenReader());

        $content = $this->saveProfileInformation(['uses_screen_reader' => true]);
        $this->assertTrue(strpos($content, 'name="uses_screen_reader" checked') !== false);
        $this->assertTrue($this->isUsingScreenReader());
    }

    public function testSuccessfulSignIn()
    {
        $this->flushSession();
        $data = ['email' => 'josh.greig2@gmail.com', 'password' => 'password'];
        $response = $this->post('/signin', $data);
        $this->assertEquals(302, $response->getStatusCode());
        $redirectUrl = $response->headers->get('Location');
        $this->assertTrue(strpos($redirectUrl, '/profile') !== false);
        $this->checkProfileAfterSignin();
        $this->checkLocationSearchRadius();
        $this->checkLocationsAddedByMe();
        $this->checkAddLocationFeature();
        $this->checkChangePasswordFeature();
        $this->checkNameChangeFeature();
        $this->checkHomeAddressFeature();
        $this->checkInternalDashboard();
        $this->checkScreenReader();
        $this->checkSuggestions();
    }

    public function testSignout()
    {
        $response = $this->get('/signout');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
