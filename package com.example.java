package com.example.kamal1234;

import static com.example.kamal1234.R.id.tvTitle;

import android.annotation.SuppressLint;
import android.os.Bundle;
import android.view.View;
import android.widget.CompoundButton;
import android.widget.ImageButton;
import android.widget.RadioButton;
import android.widget.TextView;
import android.widget.ToggleButton;

import androidx.activity.EdgeToEdge;
import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

public class MainActivity extends AppCompatActivity {
TextView tvTitle;
ImageButton ivImage;
RadioButton rb1,rb2;
ToggleButton tb;
    @SuppressLint({"MissingInflatedId", "WrongViewCast"})
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_main);
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        tvTitle=findViewById(R.id.tvTitle);
        ivImage=findViewById(R.id.ivImage);
        rb1=findViewById(R.id.rb1);
        rb2=findViewById(R.id.rb2);
        tb=findViewById(R.id.tb);

rb1.setChecked(true);
rb2.setOnClickListener(new View.OnClickListener() {
    @Override
    public void onClick(View v) {
        ivImage.setImageResource(R.drawable.ic_launcher_background);
    tvTitle.setText("image2");
    }
});
rb1.setOnClickListener(new View.OnClickListener() {
    @Override
    public void onClick(View v) {
        ivImage.setImageResource(R.drawable.ic_launcher_foreground);
    }
});
tb.setOnCheckedChangeListener(new CompoundButton.OnCheckedChangeListener() {
    @Override
    public void onCheckedChanged(@NonNull CompoundButton buttonView, boolean isChecked) {
        boolean b = false;
        if (b) {

            ivImage.setImageResource(R.drawable.ic_launcher_background);
        } else {
            ivImage.setImageResource(R.drawable.ic_launcher_foreground);

        }}});}}