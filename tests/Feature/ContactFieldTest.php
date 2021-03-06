<?php

namespace Tests\Feature;

use App\Contact;
use Tests\FeatureTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ContactFieldTest extends FeatureTestCase
{
    use DatabaseTransactions;

    /**
     * Returns an array containing a user object along with
     * a contact for that user.
     * @return array
     */
    private function fetchUser()
    {
        $user = $this->signIn();

        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
        ]);

        return [$user, $contact];
    }

    public function test_user_can_get_contact_fields()
    {
        list($user, $contact) = $this->fetchUser();

        $feild = factory(\App\ContactFieldType::class)->create([
            'account_id' => $user->account_id,
        ]);

        $contactField = factory(\App\ContactField::class)->create([
            'contact_id' => $contact->id,
            'account_id' => $user->account_id,
            'contact_field_type_id' => $feild->id,
        ]);

        $response = $this->get('/people/'.$contact->id.'/contactfield');

        $response->assertStatus(200);

        $response->assertSee($contactField->data);
    }

    public function test_user_can_get_contact_field_types()
    {
        list($user, $contact) = $this->fetchUser();

        $feild = factory(\App\ContactFieldType::class)->create([
            'account_id' => $user->account_id,
        ]);

        $response = $this->get('/people/'.$contact->id.'/contactfieldtypes');

        $response->assertStatus(200);

        $response->assertSee($feild->name);
    }

    public function test_users_can_add_contact_field()
    {
        list($user, $contact) = $this->fetchUser();

        $feild = factory(\App\ContactFieldType::class)->create([
            'account_id' => $user->account_id,
            'name' => 'Test Name',
            'type' => 'test',
        ]);

        $params = [
            'contact_field_type_id' => $feild->id,
            'data' => 'test_data',
        ];

        $response = $this->post('/people/'.$contact->id.'/contactfield', $params);

        $response->assertStatus(201);

        $params['account_id'] = $user->account_id;
        $params['contact_id'] = $contact->id;
        $params['data'] = 'test_data';

        $this->assertDatabaseHas('contact_fields', $params);

        $response = $this->get('/people/'.$contact->id.'/contactfield');

        $response->assertStatus(200);

        $response->assertSee('test_data');
    }

    public function test_users_can_edit_contact_field()
    {
        list($user, $contact) = $this->fetchUser();

        $params = ['data' => 'test_data'];

        $feild = factory(\App\ContactFieldType::class)->create([
            'account_id' => $user->account_id,
            'name' => 'Test Name',
            'type' => 'test',
        ]);

        $contactField = factory(\App\ContactField::class)->create([
            'contact_id' => $contact->id,
            'account_id' => $user->account_id,
            'contact_field_type_id' => $feild->id,
        ]);

        $params['id'] = $contactField->id;
        $params['contact_field_type_id'] = $feild->id;

        $response = $this->put('/people/'.$contact->id.'/contactfield/'.$contactField->id, $params);

        $response->assertStatus(200);

        $params['account_id'] = $user->account_id;
        $params['contact_id'] = $contact->id;
        $params['data'] = 'test_data';

        $this->assertDatabaseHas('contact_fields', $params);

        $response = $this->get('/people/'.$contact->id.'/contactfield');

        $response->assertStatus(200);

        $response->assertSee('test_data');
    }

    public function test_users_can_delete_addresses()
    {
        list($user, $contact) = $this->fetchUser();

        $feild = factory(\App\ContactFieldType::class)->create([
            'account_id' => $user->account_id,
        ]);

        $contactField = factory(\App\ContactField::class)->create([
            'contact_id' => $contact->id,
            'account_id' => $user->account_id,
            'contact_field_type_id' => $feild->id,
        ]);

        $response = $this->delete('/people/'.$contact->id.'/contactfield/'.$contactField->id);
        $response->assertStatus(200);

        $params = ['id' => $contactField->id];

        $this->assertDatabaseMissing('contact_fields', $params);
    }
}
