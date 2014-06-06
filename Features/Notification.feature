Feature: As a User
    I want to be notified when some event are triggered

Background:
  Given There is no "Goal" in database
  And There is no "User" in database

Scenario: I want to be notified when someone CHEERs a goal of mine
  Given exists an activated user "mario" with email "mario@mario.it" and password "password"
  And "mario" has the goals:
    | name                      | expire_at  | description                         | why | visibility    |
    | Conquers the country 3755 | +1 month   | Conquers the country before the end | w   | public        |
  And exists an activated user "luigi" with email "luigi@luigi.it" and password "password"
  And I am logged as "luigi@luigi.it" with password "password"
  Given I go to "/goal/mario/conquers-the-country-3755"
  And I press "Cheer"
  And I go to "/goal/mario/conquers-the-country-3755"
  And I press "Cheer"
  And workers processed all
  And workers processed all
  When I am logged as "mario@mario.it" with password "password"
  Then the "#notification-menu ul.dropdown-menu" element should contain "luigi"
  Then the "#notification-menu ul.dropdown-menu" element should contain "cheered"
  Then the "#notification-menu ul.dropdown-menu" element should contain "Conquers the country 3755"
  Then the "a.notifications span.count" element should contain "1"
  When I follow "notifications"
  Then the "#mainbig" element should contain "luigi"
  Then the "#mainbig" element should contain "(2 times)"
  Then the "#mainbig" element should contain "cheered"
  Then the "#mainbig" element should contain "Conquers the country 3755"

Scenario: I want to be notified when someone of my followings CREATE a public goal(idea)
  Given exists an activated user "mario" with email "mario@mario.it" and password "password"
  And exists an activated user "luigi" with email "luigi@luigi.it" and password "password"
  And "luigi" is following "mario"
  And I am logged as "mario" with password "password"
  Given I go to "/goal/new"
  Given I fill in "Name" with "A new idea 3753"
  And I press "Create"
  And workers processed all
  When I am logged as "luigi" with password "password"
  Then the "#notification-menu ul.dropdown-menu" element should contain "mario"
  Then the "#notification-menu ul.dropdown-menu" element should contain "created"
  Then the "#notification-menu ul.dropdown-menu" element should contain "A new idea 3753"
  Then the "a.notifications" element should contain "1"
  When I follow "notifications"
  Then the "#mainbig" element should contain "mario"
  Then the "#mainbig" element should contain "had a new idea:"
  Then the "#mainbig" element should contain "A new idea 3753"

Scenario: I want to be notified when someone of my followings START a goal(journey)
  Given exists an activated user "mario" with email "mario@mario.it" and password "password"
  And "mario" has the goals:
    | name                      | expire_in  | description                         | why | visibility    |
    | Conquers the country 3777 | 30         | Conquers the country before the end | w   | public        |
  And the goal "conquers-the-country-3777" of "mario" has "5" tasks
  And the goal "conquers-the-country-3777" of "mario" with email "mario@mario.it" and password "password", has picture "web/apple-touch-icon.png"
  And exists an activated user "luigi" with email "luigi@luigi.it" and password "password"
  And "luigi" is following "mario"
  And I am logged as "mario" with password "password"
  Given I go to "/goal/mario/conquers-the-country-3777"
  And I press "Start"
  And workers processed all
  When I am logged as "luigi" with password "password"
  Then the "#notification-menu ul.dropdown-menu" element should contain "mario"
  Then the "#notification-menu ul.dropdown-menu" element should contain "started"
  Then the "#notification-menu ul.dropdown-menu" element should contain "Conquers the country 3777"
  Then the "a.notifications" element should contain "1"
  When I follow "notifications"
  Then the "#mainbig" element should contain "mario"
  Then the "#mainbig" element should contain "started a new journey:"
  Then the "#mainbig" element should contain "Conquers the country 3777"

Scenario: I want to be notified when someone of my followings CLOSE a goal
  Given exists an activated user "mario" with email "mario@mario.it" and password "password"
  And "mario" has the goals:
    | name                      | expire_at  | description                         | why | visibility    | started_at |
    | Conquers the country 3753 | +1 month   | Conquers the country before the end | w   | public        | now        |
  And exists an activated user "luigi" with email "luigi@luigi.it" and password "password"
  And "luigi" has preference "event_goal_close_email" = "true"
  And "luigi" is following "mario"
  And I am logged as "mario" with password "password"
  Given I go to "/goal/mario/conquers-the-country-3753"
  And I follow "Close"
  Given I fill in "Learning" with "I learned a lot"
  And I select "1" from "goalLearnType[completed]"
  And I press "Set the learning of this Goal"
  And workers processed all
  Then email with subject "One of your followins closed a journey: Conquers the country 3753" should have been sent to "luigi@luigi.it"
  When I am logged as "luigi" with password "password"
  Then the "#notification-menu ul.dropdown-menu" element should contain "mario"
  Then the "#notification-menu ul.dropdown-menu" element should contain "closed"
  Then the "#notification-menu ul.dropdown-menu" element should contain "Conquers the country 3753"
  Then the "a.notifications" element should contain "1"
  When I follow "notifications"
  Then the "#mainbig" element should contain "mario"
  Then the "#mainbig" element should contain "closed"
  Then the "#mainbig" element should contain "Conquers the country 3753"

Scenario: I want to be notified when someone of my followings ADD A NOTE on his goal
  Given exists an activated user "mario" with email "mario@mario.it" and password "password"
  And "mario" has the goals:
    | name                      | expire_at  | description                         | why | visibility    | started_at |
    | Conquers the country 3763 | +1 month   | Conquers the country before the end | w   | public        | now        |
  And exists an activated user "luigi" with email "luigi@luigi.it" and password "password"
  And "luigi" is following "mario"
  And I am logged as "mario" with password "password"
  Given I go to "/goal/mario/conquers-the-country-3763"
  And I follow "Write a piece of history"
  Given I fill in "Value" with "20"
  Given I fill in "Title" with "I reached 20%"
  Given I fill in "Text" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse massa quam, "
  And I press "Add"
  And workers processed all
  When I am logged as "luigi" with password "password"
  Then the "#notification-menu ul.dropdown-menu" element should contain "mario"
  Then the "#notification-menu ul.dropdown-menu" element should contain "reached 20% on"
  Then the "#notification-menu ul.dropdown-menu" element should contain "Conquers the country 3763"
  Then the "a.notifications" element should contain "1"
  When I follow "notifications"
  Then the "#mainbig" element should contain "mario"
  Then the "#mainbig" element should contain "added a note on his journey"
  Then the "#mainbig" element should contain "Conquers the country 3763"
  Then the "#mainbig" element should contain "with 20% of completion"

Scenario: I want to be notified when someone of my followings ASK A QUESTION on his goal
  Given exists an activated user "mario" with email "mario@mario.it" and password "password"
  And "mario" has the goals:
    | name                      | expire_at  | description                         | why | visibility    | started_at |
    | Conquers the country 3163 | +1 month   | Conquers the country before the end | w   | public        | now        |
  And exists an activated user "luigi" with email "luigi@luigi.it" and password "password"
  And "luigi" has preference "event_question_create_email" = "true"
  And "mario" has preference "event_question_create_email" = "true"
  And "luigi" is following "mario"
  And I am logged as "mario" with password "password"
  Given I go to "/goal/mario/conquers-the-country-3163"
  And I follow "Ask to your friends"
  Given I fill in "questionNewType_title" with "How can I conquer Japan?"
  And I fill in "questionNewType_text" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse massa quam, "
  And I press "Create"
  And workers processed all
  Then email with subject "Hey luigi, one of your friends need you! Can you help him?" should have been sent to "luigi@luigi.it"

  When I am logged as "mario" with password "password"
  Then I should not see "mario" in the "#notification-menu ul.dropdown-menu" element
  Then I should not see "asked" in the "#notification-menu ul.dropdown-menu" element
  Then I should not see "How can I conquer Japan?" in the "#notification-menu ul.dropdown-menu" element
  Then I should not see "1" in the "a.notifications" element
  When I follow "notifications"
  Then I should not see "mario" in the "#mainbig" element
  Then I should not see "asked a question:" in the "#mainbig" element
  Then I should not see "How can I conquer Japan?" in the "#mainbig" element

  When I am logged as "luigi" with password "password"
  Then the "#notification-menu ul.dropdown-menu" element should contain "mario"
  Then the "#notification-menu ul.dropdown-menu" element should contain "asked"
  Then the "#notification-menu ul.dropdown-menu" element should contain "How can I conquer Japan?"
  Then the "a.notifications" element should contain "1"
  When I follow "notifications"
  Then the "#mainbig" element should contain "mario"
  Then the "#mainbig" element should contain "asked a question:"
  Then the "#mainbig" element should contain "How can I conquer Japan?"

Scenario: I want to be notified when someone ASK A QUESTION on my goals
  Given exists an activated user "mario" with email "mario@mario.it" and password "password"
  And "mario" has the goals:
    | name                      | expire_at  | description                         | why | visibility    | started_at |
    | Conquers the country 3103 | +1 month   | Conquers the country before the end | w   | public        | now        |
  And exists an activated user "luigi" with email "luigi@luigi.it" and password "password"
  And "luigi" has preference "event_question_create_email" = "true"
  And "mario" has preference "event_question_create_email" = "true"
  And "luigi" is following "mario"

  Given I am logged as "luigi" with password "password"
  And I go to "/goal/mario/conquers-the-country-3103"
  And I follow "Ask to your friends"
  Given I fill in "questionNewType_title" with "How can you conquer Siam?"
  And I fill in "questionNewType_text" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse massa quam, "
  And I press "Create"
  And workers processed all
  Then email with subject "Hey mario, one of your friends need you! Can you help him?" should have been sent to "mario@mario.it"

  When I am logged as "luigi" with password "password"
  Then I should not see "luigi" in the "#notification-menu ul.dropdown-menu" element
  Then I should not see "asked" in the "#notification-menu ul.dropdown-menu" element
  Then I should not see "How can you conquer Siam?" in the "#notification-menu ul.dropdown-menu" element
  Then I should not see "1" in the "a.notifications" element
  When I follow "notifications"
  Then I should not see "luigi" in the "#mainbig" element
  Then I should not see "asked a question:" in the "#mainbig" element
  Then I should not see "How can you conquer Siam?" in the "#mainbig" element

  When I am logged as "mario" with password "password"
  Then the "#notification-menu ul.dropdown-menu" element should contain "luigi"
  Then the "#notification-menu ul.dropdown-menu" element should contain "asked"
  Then the "#notification-menu ul.dropdown-menu" element should contain "How can you conquer Siam?"
  Then the "a.notifications" element should contain "1"
  When I follow "notifications"
  Then the "#mainbig" element should contain "luigi"
  Then the "#mainbig" element should contain "asked a question:"
  Then the "#mainbig" element should contain "How can you conquer Siam?"

Scenario: I don't want to be notified when someone of my followings CLOSE a private goal
  Given exists an activated user "mario" with email "mario@mario.it" and password "password"
  And "mario" has the goals:
    | name                      | expire_at  | description                         | why | visibility    | started_at |
    | Conquers the country 3754 | +1 month   | Conquers the country before the end | w   | private       | now        |
  And exists an activated user "luigi" with email "luigi@luigi.it" and password "password"
  And "luigi" is following "mario"
  And I am logged as "mario" with password "password"
  Given I go to "/goal/mario/conquers-the-country-3754"
  And I follow "Close"
  Given I fill in "Learning" with "I learned a lot"
  And I select "1" from "goalLearnType[completed]"
  And I press "Set the learning of this Goal"
  And workers processed all
  When I am logged as "luigi" with password "password"
  Then I should not see "mario" in the "#notification-menu ul.dropdown-menu" element
  Then I should not see "closed" in the "#notification-menu ul.dropdown-menu" element
  Then I should not see "Conquers the country 3754" in the "#notification-menu ul.dropdown-menu" element
  When I follow "notifications"
  Then I should not see "mario" in the "#mainbig" element
  Then I should not see "closed" in the "#mainbig" element
  Then I should not see "Conquers the country 3754" in the "#mainbig" element

Scenario: I want to be notified when someone CONGRATs for a goal of mine
  Given exists an activated user "mario" with email "mario@mario.it" and password "password"
  And "mario" has preference "event_goal_congrats_email" = "true"
  And "mario" has the goals:
    | name                      | started_at | expire_at  | description                         | why | visibility    | closed_at | completed |
    | Conquers the country 6755 | now        | +1 month   | Conquers the country before the end | w   | public        | -1 day    | true      |
  And exists an activated user "luigi" with email "luigi@luigi.it" and password "password"
  And I am logged as "luigi@luigi.it" with password "password"
  Given I go to "/goal/mario/conquers-the-country-6755"
  And I press "Congrats"
  And workers processed all
  Then email with subject "A user congratulates for your journey Conquers the country 6755" should have been sent to "mario@mario.it"
  When I am logged as "mario@mario.it" with password "password"
  Then the "#notification-menu ul.dropdown-menu" element should contain "luigi"
  Then the "#notification-menu ul.dropdown-menu" element should contain "is happy for your journey"
  Then the "#notification-menu ul.dropdown-menu" element should contain "Conquers the country 675"
  Then the "a.notifications span.count" element should contain "1"
  When I follow "notifications"
  Then the "#mainbig" element should contain "luigi"
  Then the "#mainbig" element should contain "is happy for your journey"
  Then the "#mainbig" element should contain "Conquers the country 6755"

Scenario: I want to be notified when someone FORK a goal of mine
  Given exists an activated user "mario" with email "mario@mario.it" and password "password"
  And "mario" has the goals:
    | name                      | expire_at  | description                         | why | visibility    |
    | Conquers the country 3752 | +1 month   | Conquers the country before the end | w   | public        |
  And exists an activated user "luigi" with email "luigi@luigi.it" and password "password"
  And I am logged as "luigi@luigi.it" with password "password"
  Given I go to "/goal/mario/conquers-the-country-3752"
  And I follow "Fork this Goal"
  Given I fill in "Name" with "Conquers the world 7757"
  And I fill in "Deadline" with "+ 1 month" from now
  And I fill in "Description" with "I would like to conquer the world before the end."
  And I fill in "Why" with "I will be a better dictator!"
  And I press "Create"
  And workers processed all
  When I am logged as "mario@mario.it" with password "password"
  Then the "#notification-menu ul.dropdown-menu" element should contain "luigi"
  Then the "#notification-menu ul.dropdown-menu" element should contain "created"
  Then the "#notification-menu ul.dropdown-menu" element should contain "forking"
  Then the "a.notifications span.count" element should contain "1"
  When I follow "notifications"
  Then the "#mainbig" element should contain "luigi"
  Then the "#mainbig" element should contain "created"
  Then the "#mainbig" element should contain "forking"

Scenario: I want to be notified when someone begin to FOLLOW me
  Given exists an activated user "mario" with email "mario@mario.it" and password "password"
  And "mario" has preference "event_user_follow_email" = "true"
  And exists an activated user "luigi" with email "luigi@luigi.it" and password "password"
  And I am logged as "luigi@luigi.it" with password "password"
  Given I go to "/user/mario"
  And I follow "Follow"
  And workers processed all
  Then email with subject "Hey mario, someone started to follow you!" should have been sent to "mario@mario.it"
  When I am logged as "mario@mario.it" with password "password"
  Then the "#notification-menu ul.dropdown-menu" element should contain "luigi"
  Then the "#notification-menu ul.dropdown-menu" element should contain "started to follow"
  Then I should see 1 "#notification-menu a[href='/user/luigi']" elements
  Then the "a.notifications span.count" element should contain "1"
  When I follow "notifications"
  Then the "#mainbig" element should contain "luigi"
  Then the "#mainbig" element should contain "started to follow"


Scenario: Guest register in the application
  Given I am on "/signin/"
  When I fill in "fos_user_registration_form_username" with "mario"
  And I fill in "fos_user_registration_form_email" with "sensorario@gmail.com"
  And I fill in "fos_user_registration_form_plainPassword_first" with "password"
  And I fill in "fos_user_registration_form_plainPassword_second" with "password"
  And I do not follow redirects
  And I press "Register"
  Then the response status code should be 302
  And email with subject "Welcome mario!" should have been sent to "sensorario@gmail.com"
  Then I should be redirected to "/signin/check-email"
  And the response status code should be 200
  And the response should contain "An email has been sent to sensorario@gmail.com"
  And the response should contain "It contains an activation link you must click to activate your account."