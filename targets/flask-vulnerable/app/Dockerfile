# =============================================================================
# Vulnerable Dockerfile — For Workshop Exercise Only
# DO NOT use in production! Contains intentional security misconfigurations.
# =============================================================================

# VULNERABILITY 1: Using latest tag — unpredictable, no pinned version
FROM python:3.9

# VULNERABILITY 2: Running as root (no USER directive)
# If an attacker gets code execution, they have full root access

# VULNERABILITY 3: Copying ALL files including potentially sensitive ones
COPY . /app
WORKDIR /app

# VULNERABILITY 4: Using pip with --no-vendor (default) downloads from internet
# Build should be deterministic and verified
RUN pip install --no-cache-dir -r requirements.txt

# VULNERABILITY 5: Exposing ALL dependencies (attack surface)
# requirements.txt includes intentionally outdated vulnerable packages

# VULNERABILITY 6: No HEALTHCHECK — can't monitor if app is actually healthy
# A dead or compromised container won't be detected

# VULNERABILITY 7: No resource limits (memory, CPU)
# An attacker could cause resource exhaustion

# VULNERABILITY 8: Exposing debug port alongside app port
EXPOSE 5000 5678

# VULNERABILITY 9: Using shell form (not exec form) — PID 1 is shell, not app
# Signals (SIGTERM) won't reach the application properly
CMD python app.py

# VULNERABILITY 10: No .dockerignore — .git, .env, secrets could be in the image
# Also no multi-stage build — final image contains build tools, source code

# VULNERABILITY 11: No read-only filesystem
# An attacker who gains access can write anywhere

# VULNERABILITY 12: No security options (no-new-privileges, seccomp, etc.)
# Container has full Linux capabilities
