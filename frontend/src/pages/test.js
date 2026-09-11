async function fetchProfile() {
const result = await fetch(`http://localhost:8000/api/skills`, {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                    Authorization: `Bearer 1|t23FR2XYVYaltjqA14gaXUIOD8tcFh4Z49it21nPfcf7cfbc`,
                },
                body: JSON.stringify({
                    name: 'Piggy',
                    category_skills: 'Programming',
                }),
            });
            console.log(await result.json());
}

fetchProfile();