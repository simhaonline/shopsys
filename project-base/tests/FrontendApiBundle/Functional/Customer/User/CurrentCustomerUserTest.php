<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Customer\User;

use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class CurrentCustomerUserTest extends GraphQlWithLoginTestCase
{
    public function testCurrentCustomerUser(): void
    {
        $query = '
{
    query: currentCustomerUser {
        firstName,
        lastName,
        email
        telephone
    }
}
        ';

        $jsonExpected = '
{
    "data": {
        "query": {
            "firstName": "Jaromír",
            "lastName": "Jágr",
            "email": "no-reply@shopsys.com",
            "telephone": "605000123"
        }
    }
}';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }

    public function testChangePassword(): void
    {
        $query = '
mutation {
    ChangePassword(input: {
        email: "no-reply@shopsys.com"
        oldPassword: "user123"
        newPassword: "user124"
    }) {
        firstName
        lastName
        email
        telephone
    }
}';

        $jsonExpected = '
{
    "data": {
        "query": {
            "firstName": "Jaromír",
            "lastName": "Jágr",
            "email": "no-reply@shopsys.com",
            "telephone": "605000123"
        }
    }
}';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }

    public function testChangePersonalData(): void
    {
        $query = '
mutation {
    ChangePersonalData(input: {
        telephone: "123456321"
        firstName: "John"
        lastName: "Doe"
    }) {
        firstName
        lastName,
        telephone,
        email
    }
}';

        $jsonExpected = '
{
    "data": {
        "ChangePersonalData": {
            "firstName": "John",
            "lastName": "Doe",
            "telephone": "123456321",
            "email": "no-reply@shopsys.com"
        }
    }
}';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }
}
