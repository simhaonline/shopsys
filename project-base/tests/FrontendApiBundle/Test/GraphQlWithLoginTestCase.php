<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Test;

use Symfony\Component\HttpFoundation\Response;

abstract class GraphQlWithLoginTestCase extends GraphQlTestCase
{
    public const DEFAULT_USER_EMAIL = 'no-reply@shopsys.com';
    public const DEFAULT_USER_PASSWORD = 'user123';

    /**
     * @param string $query
     * @param string $jsonExpected
     * @param string $jsonVariables
     * @param string|null $customerUserEmail
     * @param string|null $customerUserPassword
     */
    protected function assertQueryWithExpectedJson(string $query, string $jsonExpected, $jsonVariables = '{}', ?string $customerUserEmail = null, ?string $customerUserPassword = null): void
    {
        $this->assertQueryWithExpectedArray($query, json_decode($jsonExpected, true), json_decode($jsonVariables, true), $customerUserEmail, $customerUserPassword);
    }

    /**
     * @param string $query
     * @param array $expected
     * @param array $variables
     * @param string|null $customerUserEmail
     * @param string|null $customerUserPassword
     */
    protected function assertQueryWithExpectedArray(string $query, array $expected, array $variables = [], ?string $customerUserEmail = null, ?string $customerUserPassword = null): void
    {
        $response = $this->getResponseForQuery($query, $variables, [], $customerUserEmail, $customerUserPassword);

        $this->assertSame(200, $response->getStatusCode());

        $result = $response->getContent();
        $this->assertEquals($expected, json_decode($result, true), $result);
    }

    /**
     * @param string $query
     * @param array $variables
     * @param array $customServer
     * @param string|null $customerUserEmail
     * @param string|null $customerUserPassword
     * @return array
     */
    protected function getResponseContentForQuery(string $query, array $variables = [], array $customServer = [], ?string $customerUserEmail = null, ?string $customerUserPassword = null): array
    {
        $content = $this->getResponseForQuery($query, $variables, $customServer, $customerUserEmail, $customerUserPassword)->getContent();

        return json_decode($content, true);
    }

    /**
     * @param string $query
     * @param array $variables
     * @param array $customServer
     * @param string|null $customerUserEmail
     * @param string|null $customerUserPassword
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function getResponseForQuery(string $query, array $variables, array $customServer = [], ?string $customerUserEmail = null, ?string $customerUserPassword = null): Response
    {
        $path = $this->getLocalizedPathOnFirstDomainByRouteName('overblog_graphql_endpoint');
        $server = array_merge(
            ['CONTENT_TYPE' => 'application/graphql', 'HTTP_Authorization' => sprintf('Bearer %s', $this->getAccessToken($customerUserEmail, $customerUserPassword))],
            $customServer
        );

        $this->client->request(
            'GET',
            $path,
            ['query' => $query, 'variables' => json_encode($variables)],
            [],
            $server
        );

        return $this->client->getResponse();
    }

    /**
     * @param string|null $customerUserEmail
     * @param string|null $customerUserPassword
     * @return string
     */
    private function getAccessToken(?string $customerUserEmail = null, ?string $customerUserPassword = null): string
    {
        $responseData = parent::getResponseContentForQuery($this->getLoginQuery($customerUserEmail, $customerUserPassword));

        return $responseData['data']['Login']['accessToken'];
    }

    /**
     * @param string|null $customerUserEmail
     * @param string|null $customerUserPassword
     * @return string
     */
    private static function getLoginQuery(?string $customerUserEmail = null, ?string $customerUserPassword = null): string
    {
        $customerUserEmail = $customerUserEmail === null ? self::DEFAULT_USER_EMAIL : $customerUserEmail;
        $customerUserPassword = $customerUserPassword === null ? self::DEFAULT_USER_PASSWORD : $customerUserPassword;

        return '
            mutation {
                Login(input: {
                    email: "' . $customerUserEmail . '"
                    password: "' . $customerUserPassword . '"
                }) {
                    accessToken
                    refreshToken
                }
            }
        ';
    }
}
