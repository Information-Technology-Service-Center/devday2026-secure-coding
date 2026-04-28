/**
 * Frontend config — Intentionally vulnerable for workshop exercise
 */

// VULNERABILITY: Hardcoded API keys and secrets
const config = {
    apiUrl: "https://api.example.com",
    apiKey: "AKIAIOSFODNN7EXAMPLE",
    secretKey: "wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY",
    dbPassword: "mysql_p@ssw0rd_2024!",
    jwtSecret: "my-super-secret-jwt-key-that-should-not-be-here",
    stripeKey: "sk_live_EXAMPLE_REPLACE_THIS_WITH_REAL_KEY",
    githubToken: "ghp_EXAMPLE_REPLACE_THIS_WITH_REAL_TOKEN_HERE",
    slackWebhook: "https://hooks.slack.example.com/services/TXXXXX/BXXXXX/XXXXXXXXXX",
};

module.exports = config;
