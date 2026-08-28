$(function () {
  if (!initLayout()) return;

  api("GET", "/me.php").done(function (res) {
    const u = res.user || {};
    $("#uName").text(u.username || "-");
    $("#uRole").text(u.role || "-");
    $("#uCreated").text(u.created_at || "-");
    if (u.role === "admin") {
      $("#lineBox").removeClass("d-none");
      loadLine();
    }
  });

  api("GET", "/health.php").done(function (res) {
    $("#hMsg").text(res.success ? "เชื่อมต่อได้" : "มีปัญหา");
    $("#hDb").text(res.database || "-");
    $("#hTime").text(res.server_time || "-");
  }).fail(function () {
    $("#hMsg").text("เชื่อมต่อ API ไม่ได้");
  });

  $("#lineSave").on("click", function () { saveLine(false); });
  $("#lineTest").on("click", function () { saveLine(true); });
});

function linePayload(action) {
  return {
    action: action,
    enabled: $("#lineEnabled").is(":checked"),
    channel_token: $.trim($("#lineToken").val()),
    user_id: $.trim($("#lineUser").val()),
  };
}

function loadLine() {
  api("GET", "/line.php").done(function (res) {
    $("#lineEnabled").prop("checked", !!res.enabled);
    $("#lineUser").val(res.user_id || "");
    $("#lineTokenHint").text(
      res.has_token ? "บันทึก token แล้ว (... " + res.token_tail + ")" : "ยังไม่ได้บันทึก token"
    );
  });
}

function saveLine(thenTest) {
  const userId = $.trim($("#lineUser").val());
  if (userId && !/^[UCR]/i.test(userId)) {
    toast("User ID ต้องขึ้นต้นด้วย U หรือ Group ID ขึ้นต้นด้วย C — ตอนนี้ใส่ชื่อเล่นอยู่ เว้นว่างไว้ก่อนได้", "danger");
    return;
  }

  api("POST", "/line.php", linePayload(thenTest ? "test" : "save"))
    .done(function (res) {
      toast(res.message || (thenTest ? "ส่งแจ้งเตือนแล้ว" : "บันทึกแล้ว"));
      if (!thenTest) $("#lineToken").val("");
      loadLine();
    })
    .fail(function (xhr) {
      toast((xhr.responseJSON && xhr.responseJSON.message) || "ไม่สำเร็จ", "danger");
    });
}
