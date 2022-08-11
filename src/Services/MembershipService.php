<?php
    namespace DxlApi\Services;

    use DxlApi\Interfaces\ServiceInterface;

    use DxlMemberships\Classes\Repositories\MemberRepository;
    use DxlMemberships\Classes\Repositories\MembershipRepository;

    if( ! class_exists('MembershipService') )
    {
        class MembershipService implements ServiceInterface
        {
            /**
             * Calculating membership expiration date
             *
             * @return string
             */
            public function calculateMembershipExpiration($member, $membership)
            {
                if( $membership->length == '6' )
                {
                    $expiration = date('Y-m-d', strtotime('last day of june this year'));
                }
                else if( $membership->length == '12' )
                {
                    $expiration = date('Y-m-d', strtotime('last day of december this year'));
                }

                return $expiration;
            }
        }
    }