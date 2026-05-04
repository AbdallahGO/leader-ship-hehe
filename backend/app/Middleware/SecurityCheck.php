<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ActivityLoggingService;

/**
 * Security Check Middleware
 *
 * Detects and logs suspicious activities
 * - Unusual access patterns
 * - Potential SQL injection attempts
 * - Potential XSS attempts
 * - Unauthorized access attempts
 */
class SecurityCheck
{
    /**
     * Activity logging service
     */
    private ActivityLoggingService $activityLogger;

    /**
     * Patterns that indicate potential attacks
     */
    private array $suspiciousPatterns = [
        'sql_injection' => [
            "/'.*or.*'.*=/i",
            '/union.*select/i',
            '/insert.*into/i',
            '/delete.*from/i',
            '/drop.*table/i',
            '/update.*set/i',
            '/exec\(/i',
            '/execute\(/i',
        ],
        'xss_attempt' => [
            '/<script/i',
            '/<iframe/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<svg/i',
            '/<img.*on/i',
        ],
    ];

    /**
     * Create a new middleware instance.
     */
    public function __construct(ActivityLoggingService $activityLogger)
    {
        $this->activityLogger = $activityLogger;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $this->checkForSuspiciousActivity($request);

        return $next($request);
    }

    /**
     * Check request for suspicious patterns
     */
    private function checkForSuspiciousActivity(Request $request): void
    {
        $requestData = array_merge(
            $request->query(),
            $request->all()
        );

        foreach ($this->suspiciousPatterns as $attackType => $patterns) {
            foreach ($requestData as $key => $value) {
                if (!is_string($value)) {
                    continue;
                }

                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        $this->logSuspiciousActivity($request, $attackType, $key, $value);
                    }
                }
            }
        }
    }

    /**
     * Log suspicious activity
     */
    private function logSuspiciousActivity(
        Request $request,
        string $attackType,
        string $parameter,
        string $value
    ): void {
        $userId = auth()->id();

        $this->activityLogger->logSuspiciousActivity($attackType, json_encode([
            'parameter' => $parameter,
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'value_snippet' => substr($value, 0, 100),
            'ip_address' => $request->ip(),
            'user_id' => $userId,
        ]));
    }
}
