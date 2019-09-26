<?php 
require_once(__DIR__ .'/../includes/autoload.php');

$status = $msg = $option = $response = '';

$card = '
<div class="pc-card__div">
    <a class="pc-card__bg pc-card__block"></a>

	<div>
		<div class="pc-card__button">
            <button>Follow</button> 
		</div>

		<a title="Mert S. Kaplan" href="https://twitter.com/mertskaplan" class="pc-card__avatarLink">
			<img alt="Mert S. Kaplan" src="https://mertskaplan.com/wp-content/plugins/msk-twprofilecard/img/mertskaplan.jpg" class="pc-card__avatarImg">
		</a>

		<div class="pc-card__divUser">
			<div class="pc-card__divName">
				<a href="https://twitter.com/mertskaplan">Mert S. Kaplan</a>
			</div>
			<span>
				<a href="https://twitter.com/mertskaplan">@<span>mertskaplan</span></a>
			</span>
		</div>

		<div class="pc-card__divStats">
			<ul class="pc-card__Arrange">
				<li class="pc-card__ArrangeSizeFit">
					<a href="https://twitter.com/mertskaplan" title="9.840 Tweet">
						<span class="pc-card__StatLabel pc-card__block">Tweets</span>
						<span class="pc-card__StatValue">9.840</span>
					</a>
				</li>
				<li class="pc-card__ArrangeSizeFit">
					<a href="https://twitter.com/mertskaplan/following" title="885 Following">
						<span class="pc-card__StatLabel pc-card__block">Following</span>
						<span class="pc-card__StatValue">885</span>
					</a>
				</li>
				<li class="pc-card__ArrangeSizeFit">
					<a href="https://twitter.com/mertskaplan/followers" title="1.810 Followers">
						<span class="pc-card__StatLabel pc-card__block">Followers</span>
						<span class="pc-card__StatValue">1.810</span>
					</a>
				</li>
			</ul>
		</div>
	</div>
</div>';
$response = $card;

$data = array('status' => $status, 'msg' => $msg, 'option' => $option, 'html' => $response);
echo json_encode($data, JSON_UNESCAPED_SLASHES); 
