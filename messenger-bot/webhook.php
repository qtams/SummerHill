<?php
$verify_token = "summerhill_secret_token";
$access_token = "EAALCxdA8ZBOsBPCPi537k1gBepA2HuGlA9JR3PlSmhYwpZCLTqYYgC9eE1I2Jham9BvOSbgB6FuRBRr8xXR9gt1ZC1XZC2zmuo7Mm2CChtgUZCwp2yvpO9VxnU8pIz5zONim7NZBhhXnZCXKDBymOkM2qVJSdspfyV9PuIa4goT6TLPxtw9Ko2W2nq6nYTACe9M5qRLxUNrpwZDZD";

include "../databases/connection.php"; // $con is your MySQLi connection

function logDbError($context, $con)
{
    $errorMsg = "[" . date('Y-m-d H:i:s') . "] $context: " . $con->error . PHP_EOL;
    file_put_contents('db_error_log.txt', $errorMsg, FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (
        isset($_GET['hub_mode']) && $_GET['hub_mode'] === 'subscribe' &&
        isset($_GET['hub_verify_token']) && $_GET['hub_verify_token'] === $verify_token
    ) {
        echo $_GET['hub_challenge'];
        exit;
    } else {
        echo "Invalid verify token.";
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    file_put_contents('log.txt', $input . PHP_EOL, FILE_APPEND);

    $data = json_decode($input, true);

    if (!empty($data['entry'])) {
        foreach ($data['entry'] as $entry) {
            if (!empty($entry['messaging'])) {
                foreach ($entry['messaging'] as $event) {
                    $senderId = $event['sender']['id'] ?? null;
                    $response = [];

                    // 🔹 Handle Get Started Button
                    if (isset($event['postback']['payload']) && $event['postback']['payload'] === 'GET_STARTED') {
                        $response = [
                            'recipient' => ['id' => $senderId],
                            'message' => [
                                'text' => "Welcome to SummerHill! 😊 Would you like to receive daily updates about your child's attendance?",
                                'quick_replies' => [
                                    [
                                        'content_type' => 'text',
                                        'title' => 'Yes, please',
                                        'payload' => 'SUBSCRIBE_ATTENDANCE'
                                    ],
                                    [
                                        'content_type' => 'text',
                                        'title' => 'No, thanks',
                                        'payload' => 'UNSUBSCRIBE_ATTENDANCE'
                                    ]
                                ]
                            ]
                        ];

                        // 🔹 Handle Quick Replies
                    } elseif (isset($event['message']['quick_reply']['payload'])) {
                        $payload = $event['message']['quick_reply']['payload'];

                        if ($payload === 'SUBSCRIBE_ATTENDANCE') {
                            // Check if user is already in pending_subscriptions
                            $checkPending = $con->prepare("SELECT pending_step FROM pending_subscriptions WHERE social = ?");
                            $checkPending->bind_param("s", $senderId);
                            if ($checkPending->execute()) {
                                $pendingResult = $checkPending->get_result();
                                if ($pendingResult->num_rows > 0) {
                                    // User already in pending process
                                    $response = [
                                        'recipient' => ['id' => $senderId],
                                        'message' => ['text' => "Please complete your subscription process first. What's your child's Student ID?"]
                                    ];
                                } else {
                                    // Start new subscription process
                                    $insertPending = $con->prepare("INSERT INTO pending_subscriptions (social, pending_step, created_at) VALUES (?, 'student_id', NOW())");
                                    $insertPending->bind_param("s", $senderId);
                                    if ($insertPending->execute()) {
                                        $response = [
                                            'recipient' => ['id' => $senderId],
                                            'message' => ['text' => "Great! 😊 Please reply with your child's Student ID to complete the subscription."]
                                        ];
                                    } else {
                                        logDbError("Insert pending subscription", $con);
                                        $response = [
                                            'recipient' => ['id' => $senderId],
                                            'message' => ['text' => "⚠️ Something went wrong. Please try again later."]
                                        ];
                                    }
                                    $insertPending->close();
                                }
                                $pendingResult->free();
                            } else {
                                logDbError("Check pending subscription", $con);
                            }
                            $checkPending->close();
                        } elseif ($payload === 'UNSUBSCRIBE_ATTENDANCE') {
                            // Remove from pending_subscriptions if exists
                            $deletePending = $con->prepare("DELETE FROM pending_subscriptions WHERE social = ?");
                            $deletePending->bind_param("s", $senderId);
                            $deletePending->execute();
                            $deletePending->close();

                            $update = $con->prepare("UPDATE students SET email = NULL, social = NULL, mobile = NULL, pending_step = NULL, is_subscribed = 0 WHERE social = ?");
                            $update->bind_param("s", $senderId);
                            if ($update->execute()) {
                                $response = [
                                    'recipient' => ['id' => $senderId],
                                    'message' => ['text' => "✅ You've been unsubscribed from attendance updates. If you change your mind, just send 'subscribe'."]
                                ];
                            } else {
                                logDbError("Quick reply unsubscribe", $con);
                                $response = [
                                    'recipient' => ['id' => $senderId],
                                    'message' => ['text' => "⚠️ Something went wrong. Please try again later."]
                                ];
                            }
                            $update->close();
                        }

                        // 🔹 Handle normal text input
                    } elseif (isset($event['message']['text'])) {
                        $messageText = trim($event['message']['text']);
                        $lowerText = strtolower($messageText);

                        // 🔸 Unsubscribe via text
                        if (in_array($lowerText, ['unsubscribe', 'stop', 'cancel'])) {
                            // Remove from pending_subscriptions if exists
                            $deletePending = $con->prepare("DELETE FROM pending_subscriptions WHERE social = ?");
                            $deletePending->bind_param("s", $senderId);
                            $deletePending->execute();
                            $deletePending->close();

                            // Check if user is actually subscribed
                            $check = $con->prepare("SELECT student_id FROM students WHERE social = ?");
                            $check->bind_param("s", $senderId);
                            if ($check->execute()) {
                                $res = $check->get_result();
                                if ($res->num_rows > 0) {
                                    // User is subscribed, proceed to unsubscribe
                                    $update = $con->prepare("UPDATE students SET social = NULL, pending_step = NULL, mobile = NULL, email = NULL, is_subscribed = 0 WHERE social = ?");
                                    $update->bind_param("s", $senderId);
                                    if ($update->execute()) {
                                        $response = [
                                            'recipient' => ['id' => $senderId],
                                            'message' => ['text' => "✅ You've been unsubscribed. If you want to subscribe again, type 'subscribe' or click Get Started."]
                                        ];
                                    } else {
                                        logDbError("Manual unsubscribe via text", $con);
                                        $response = [
                                            'recipient' => ['id' => $senderId],
                                            'message' => ['text' => "⚠️ Something went wrong. Please try again later."]
                                        ];
                                    }
                                    $update->close();
                                } else {
                                    // User is not subscribed
                                    $response = [
                                        'recipient' => ['id' => $senderId],
                                        'message' => ['text' => "You are not currently subscribed. If you want to subscribe, type 'subscribe' or click Get Started."]
                                    ];
                                }
                                $res->free();
                            } else {
                                logDbError("Check subscription before unsubscribe", $con);
                            }
                            $check->close();

                            // 🔸 Subscribe again manually
                        } elseif ($lowerText === 'subscribe') {
                            $response = [
                                'recipient' => ['id' => $senderId],
                                'message' => [
                                    'text' => "Welcome back! 😊 Would you like to receive daily updates about your child's attendance?",
                                    'quick_replies' => [
                                        [
                                            'content_type' => 'text',
                                            'title' => 'Yes, please',
                                            'payload' => 'SUBSCRIBE_ATTENDANCE'
                                        ],
                                        [
                                            'content_type' => 'text',
                                            'title' => 'No, thanks',
                                            'payload' => 'UNSUBSCRIBE_ATTENDANCE'
                                        ]
                                    ]
                                ]
                            ];
                        } else {
                            // 🔸 First check if user is in pending_subscriptions
                            $checkPending = $con->prepare("SELECT pending_step FROM pending_subscriptions WHERE social = ?");
                            $checkPending->bind_param("s", $senderId);
                            if ($checkPending->execute()) {
                                $pendingResult = $checkPending->get_result();
                                if ($pendingResult->num_rows > 0) {
                                    // User is in pending subscription process
                                    $pendingRow = $pendingResult->fetch_assoc();
                                    $pendingStep = $pendingRow['pending_step'];

                                    if ($pendingStep === 'student_id') {
                                        // Validate student ID
                                        $stmt = $con->prepare("SELECT student_id, social FROM students WHERE student_id = ?");
                                        $stmt->bind_param("s", $messageText);
                                        if ($stmt->execute()) {
                                            $result = $stmt->get_result();
                                            if ($result->num_rows > 0) {
                                                $row = $result->fetch_assoc();
                                                if (!empty($row['social'])) {
                                                    // A parent is already subscribed
                                                    $response = [
                                                        'recipient' => ['id' => $senderId],
                                                        'message' => ['text' => "⚠️ This student ID is already subscribed by another parent. If you believe this is an error, please contact the school."]
                                                    ];
                                                    // Remove from pending
                                                    $deletePending = $con->prepare("DELETE FROM pending_subscriptions WHERE social = ?");
                                                    $deletePending->bind_param("s", $senderId);
                                                    $deletePending->execute();
                                                    $deletePending->close();
                                                } else {
                                                    // Proceed to link student to new sender
                                                    $update = $con->prepare("UPDATE students SET social = ?, pending_step = 'mobile' WHERE student_id = ?");
                                                    $update->bind_param("ss", $senderId, $messageText);
                                                    if ($update->execute()) {
                                                        // Update pending_subscriptions to next step
                                                        $updatePending = $con->prepare("UPDATE pending_subscriptions SET pending_step = 'mobile' WHERE social = ?");
                                                        $updatePending->bind_param("s", $senderId);
                                                        $updatePending->execute();
                                                        $updatePending->close();

                                                        $response = [
                                                            'recipient' => ['id' => $senderId],
                                                            'message' => ['text' => "📱 Great! Now please enter the parent's mobile number:"]
                                                        ];
                                                    } else {
                                                        logDbError("Link student ID to social", $con);
                                                    }
                                                    $update->close();
                                                }
                                            } else {
                                                $response = [
                                                    'recipient' => ['id' => $senderId],
                                                    'message' => ['text' => "❌ Sorry, Student ID not found. Please double-check and try again."]
                                                ];
                                            }
                                            $result->free();
                                        } else {
                                            logDbError("Check student ID input", $con);
                                        }
                                        $stmt->close();
                                    } elseif ($pendingStep === 'mobile') {
                                        // Update students table with mobile
                                        $update = $con->prepare("UPDATE students SET mobile = ? WHERE social = ?");
                                        $update->bind_param("ss", $messageText, $senderId);
                                        if ($update->execute()) {
                                            // Update pending_subscriptions to next step
                                            $updatePending = $con->prepare("UPDATE pending_subscriptions SET pending_step = 'email' WHERE social = ?");
                                            $updatePending->bind_param("s", $senderId);
                                            $updatePending->execute();
                                            $updatePending->close();

                                            $response = [
                                                'recipient' => ['id' => $senderId],
                                                'message' => ['text' => "📧 Thanks! Now, please enter your email address:"]
                                            ];
                                        } else {
                                            logDbError("Update mobile", $con);
                                        }
                                        $update->close();
                                    } elseif ($pendingStep === 'email') {
                                        // Update students table with email and complete subscription
                                        $update = $con->prepare("UPDATE students SET email = ?, pending_step = NULL, is_subscribed = 1 WHERE social = ?");
                                        $update->bind_param("ss", $messageText, $senderId);
                                        if ($update->execute()) {
                                            // Remove from pending_subscriptions
                                            $deletePending = $con->prepare("DELETE FROM pending_subscriptions WHERE social = ?");
                                            $deletePending->bind_param("s", $senderId);
                                            $deletePending->execute();
                                            $deletePending->close();

                                            $response = [
                                                'recipient' => ['id' => $senderId],
                                                'message' => ['text' => "✅ All set! You'll now receive attendance updates daily. Thank you!"]
                                            ];
                                        } else {
                                            logDbError("Update email", $con);
                                        }
                                        $update->close();
                                    }
                                    $pendingResult->free();
                                } else {
                                    // User not in pending - check if already subscribed
                                    $check = $con->prepare("SELECT student_id, pending_step FROM students WHERE social = ?");
                                    $check->bind_param("s", $senderId);
                                    if ($check->execute()) {
                                        $res = $check->get_result();
                                        if ($res->num_rows > 0) {
                                            $row = $res->fetch_assoc();
                                            $studentId = $row['student_id'];
                                            $step = $row['pending_step'];

                                            if ($step === 'mobile') {
                                                $update = $con->prepare("UPDATE students SET mobile = ?, pending_step = 'email' WHERE student_id = ?");
                                                $update->bind_param("ss", $messageText, $studentId);
                                                if ($update->execute()) {
                                                    $response = [
                                                        'recipient' => ['id' => $senderId],
                                                        'message' => ['text' => "📧 Thanks! Now, please enter your email address:"]
                                                    ];
                                                } else {
                                                    logDbError("Update mobile", $con);
                                                }
                                                $update->close();
                                            } elseif ($step === 'email') {
                                                $update = $con->prepare("UPDATE students SET email = ?, pending_step = NULL, is_subscribed = 1 WHERE student_id = ?");
                                                $update->bind_param("ss", $messageText, $studentId);
                                                if ($update->execute()) {
                                                    $response = [
                                                        'recipient' => ['id' => $senderId],
                                                        'message' => ['text' => "✅ All set! You'll now receive attendance updates daily. Thank you!"]
                                                    ];
                                                } else {
                                                    logDbError("Update email", $con);
                                                }
                                                $update->close();
                                            } else {
                                                $response = [
                                                    'recipient' => ['id' => $senderId],
                                                    'message' => ['text' => "You're already subscribed. ✅"]
                                                ];
                                            }
                                            $res->free();
                                        } else {
                                            // User not subscribed - prompt to subscribe
                                            $response = [
                                                'recipient' => ['id' => $senderId],
                                                'message' => [
                                                    'text' => "Welcome to SummerHill! 😊 Would you like to receive daily updates about your child's attendance?",
                                                    'quick_replies' => [
                                                        [
                                                            'content_type' => 'text',
                                                            'title' => 'Yes, please',
                                                            'payload' => 'SUBSCRIBE_ATTENDANCE'
                                                        ],
                                                        [
                                                            'content_type' => 'text',
                                                            'title' => 'No, thanks',
                                                            'payload' => 'UNSUBSCRIBE_ATTENDANCE'
                                                        ]
                                                    ]
                                                ]
                                            ];
                                        }
                                        $check->close();
                                    } else {
                                        logDbError("Check social mapping", $con);
                                    }
                                }
                                $checkPending->close();
                            } else {
                                logDbError("Check pending subscription", $con);
                            }
                        }
                    }

                    // 🔹 Send the response
                    if (!empty($response)) {
                        $ch = curl_init("https://graph.facebook.com/v17.0/me/messages?access_token=$access_token");
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
                        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $result = curl_exec($ch);
                        curl_close($ch);

                        file_put_contents('log.txt', "RESPONSE: $result" . PHP_EOL, FILE_APPEND);
                    }
                }
            }
        }
    }

    http_response_code(200);
    echo "EVENT_RECEIVED";
}
