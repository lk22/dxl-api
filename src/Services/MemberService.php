<?php 
    namespace DxlApi\Services;

    use DxlApi\Interfaces\ServiceInterface;

    use DxlApi\Exceptions\MemberValidationException;

    use DxlMembership\Classes\Repositories\MemberRepository;

    if( !class_exists('MemberService') ) 
    {
        class MemberService implements ServiceInterface
        {

            public $memberRepository;

            public function __construct()
            {
                $this->memberRepository = new MemberRepository();
            }

            /**
             * Validate member existance from giving information
             *
             * @param [type] ...$member
             * @return void
             */
            public function validateParticipant(string $email, string $gamertag) {
                $validation = [];

                $member = $this->memberRepository
                    ->select()
                    ->where('email', "'$email'")
                    ->whereOr('gamertag', "'$gamertag'")
                    ->getRow();

                return $member ?? false;
            }
        }
    }
?>