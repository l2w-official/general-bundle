<?php

namespace LearnToWin\GeneralBundle\Constants;

class OrganizationRole
{
    // Can make administrative changes to the organization
    public const ORG_ROLE_ADMIN = 'ORG_ROLE_ADMIN'; // 0

    // Can make administrative changes to other users in the same organizations
    public const ORG_ROLE_USER_ADMIN = 'ORG_ROLE_USER_ADMIN'; // 1

    // Used externally to limit what this user can create/update in the organizations
    public const ORG_ROLE_CONTENT_CREATOR = 'ORG_ROLE_CONTENT_CREATOR'; // 2

    // Used externally to identify that this user can be enrolled/invited to courses.
    public const ORG_ROLE_LEARNER = 'ORG_ROLE_LEARNER'; // 3

    // Can view reports for users in the organization
    public const ORG_ROLE_ANALYST = 'ORG_ROLE_ANALYST'; // 4
}
