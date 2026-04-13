async function completeChallenge(challengeId) {
    const { childId, csrfToken, childName } = window.challengeConfig;
    console.log('Sending:', { child_id: childId, challenge_id: challengeId, stars: 1, csrf_token: csrfToken });
    try {
        const response = await fetch('/pre_edu_platform/log_challenge_completion.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ child_id: childId, challenge_id: challengeId, stars: 1, csrf_token: csrfToken })
        });
        const result = await response.json();
        console.log('Response:', result);
        if (result.status === 'success') {
            alert('Well done! ⭐');
            window.location.href = `/pre_edu_platform/child_dashboard.php?childName=${childName}`;
        } else {
            alert(result.message || 'Failed to log challenge');
        }
    } catch (error) {
        console.error('Error completing challenge:', error);
        alert('An error occurred while logging the challenge');
    }
}